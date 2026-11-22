<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Creates an unpaid order. Stock is NOT deducted here: it is only reserved (see
     * MenuItem::reservedQuantities) until the payment window closes, and deducted for real
     * when the payment settles (PaymentService::commitStock).
     */
    public function place(User $user, array $data, array $basket): Order
    {
        $lines = collect($basket)
            ->groupBy('slug')
            ->map(fn($rows) => (int) $rows->sum('qty'));

        return DB::transaction(function () use ($user, $data, $lines) {
            // Locking the rows makes concurrent checkouts of the same item wait for each other,
            // so the "stock minus reserved" check below cannot be raced.
            $menuItems = MenuItem::query()
                ->whereIn('slug', $lines->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('slug');

            $reserved = MenuItem::reservedQuantities($menuItems->pluck('id'));

            $subtotal = 0;

            foreach ($lines as $slug => $qty) {
                $menuItem = $menuItems->get($slug);

                $available = $menuItem === null
                    ? 0
                    : $menuItem->stock - ($reserved[$menuItem->id] ?? 0);

                if ($menuItem === null || $menuItem->status !== 'active' || $available < $qty) {
                    throw ValidationException::withMessages([
                        'items' => __(':name is unavailable or has insufficient stock.', [
                            'name' => $menuItem?->name ?? $slug,
                        ]),
                    ]);
                }

                $subtotal += $menuItem->price * $qty;
            }

            $shippingFee = Order::SHIPPING_FEES[$data['delivery_type']];
            $isPreOrder = $data['delivery_type'] === 'pre_order';

            $order = Order::create([
                'id_pesanan' => Order::generateCode(),
                'user_id' => $user->id,
                'customer_name' => $data['name'],
                'customer_email' => $user->email,
                'customer_phone' => $data['phone'],
                'address' => $data['address'],
                'delivery_type' => $data['delivery_type'],
                'delivery_date' => $isPreOrder ? $data['delivery_date'] : now(Order::TIMEZONE)->toDateString(),
                'delivery_slot' => $isPreOrder ? $data['delivery_slot'] : null,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
                'status' => Order::STATUS_WAITING,
            ]);

            foreach ($lines as $slug => $qty) {
                $menuItem = $menuItems[$slug];

                $order->items()->create([
                    'menu_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'price' => $menuItem->price,
                    'quantity' => $qty,
                ]);
            }

            return $order->load('items');
        });
    }

    /**
     * Rejects an order. Stock is only given back if it was actually deducted, i.e. the order
     * had been paid. Rejecting an unpaid order releases its reservation just by leaving the
     * "waiting" status, so there is nothing to restore.
     */
    /**
     * Shipped -> done. Normally the customer confirms receipt; orders nobody confirms are closed by the
     * orders:complete-shipped command. Only a shipped order can move, and completed_at (the start of the
     * 24-hour refund window) is set here and nowhere else.
     *
     * @param  int|null  $userId  when given, only that customer's order can be completed
     */
    public function markDone(int $orderId, ?int $userId = null): bool
    {
        return Order::query()
            ->whereKey($orderId)
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->where('status', Order::STATUS_SHIPPED)
            ->update([
                'status' => Order::STATUS_DONE,
                'completed_at' => now(),
                'updated_at' => now(),
            ]) === 1;
    }

    public function reject(int $orderId, string $reason, array $allowedFrom = [Order::STATUS_WAITING]): ?Order
    {
        return DB::transaction(function () use ($orderId, $reason, $allowedFrom) {
            $order = Order::with(['items', 'transaction'])->lockForUpdate()->find($orderId);

            if ($order === null || !in_array($order->status, $allowedFrom, true)) {
                return null;
            }

            $stockWasDeducted = $order->transaction?->waktu_bayar !== null;

            if ($stockWasDeducted) {
                foreach ($order->items as $item) {
                    if ($item->menu_item_id !== null) {
                        MenuItem::whereKey($item->menu_item_id)->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => Order::STATUS_REJECTED,
                'rejection_reason' => $reason,
            ]);

            return $order;
        });
    }
}