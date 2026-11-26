<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function place(User $user, array $data, array $basket, array $quote): Order
    {
        $lines = collect($basket)
            ->groupBy('slug')
            ->map(fn($rows) => (int) $rows->sum('qty'));

        return DB::transaction(function () use ($user, $data, $lines, $quote) {
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

            $shippingFee = $quote['fee'];
            $address = $quote['address'];
            $isPreOrder = $data['delivery_type'] === 'pre_order';

            $order = Order::create([
                'id_pesanan' => Order::generateCode(),
                'user_id' => $user->id,
                'customer_name' => $data['name'],
                'customer_email' => $user->email,
                'customer_phone' => $data['phone'],
                'address' => $address,
                'distance_km' => $quote['distance_km'],
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
    /** $userId set = the customer confirmed receipt; null = the scheduler completed it automatically. */
    public function markDone(int $orderId, ?int $userId = null): bool
    {
        $done = Order::query()
            ->whereKey($orderId)
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->where('status', Order::STATUS_SHIPPED)
            ->update([
                'status' => Order::STATUS_DONE,
                'completed_at' => now(),
                'updated_at' => now(),
            ]) === 1;

        if ($done && ($order = Order::find($orderId)) !== null) {
            app(NotificationService::class)->orderCompleted($order, byCustomer: $userId !== null);
        }

        return $done;
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