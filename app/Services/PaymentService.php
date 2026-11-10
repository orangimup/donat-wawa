<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function configureMidtrans(): void
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    /**
     * Midtrans signs every notification: sha512(order_id + status_code + gross_amount + server_key).
     * Without this check anyone could POST "settlement" for an unpaid order.
     */
    public function hasValidSignature(array $payload): bool
    {
        $serverKey = (string) config('midtrans.server_key');

        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $key) {
            if (! isset($payload[$key]) || ! is_scalar($payload[$key])) {
                return false;
            }
        }

        $expected = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $serverKey);

        return $serverKey !== '' && hash_equals($expected, (string) $payload['signature_key']);
    }

    /** Midtrans transaction_status -> our transactions.status (null = ignore). */
    public function mapStatus(array $payload): ?string
    {
        $status = $payload['transaction_status'] ?? null;
        $fraud = $payload['fraud_status'] ?? null;

        return match ($status) {
            'capture' => $fraud === 'challenge' ? Transaction::STATUS_PENDING : Transaction::STATUS_SETTLED,
            'settlement' => Transaction::STATUS_SETTLED,
            'pending' => Transaction::STATUS_PENDING,
            'deny', 'failure' => Transaction::STATUS_FAILED,
            'cancel' => Transaction::STATUS_CANCELLED,
            'expire' => Transaction::STATUS_EXPIRED,
            'refund', 'partial_refund' => Transaction::STATUS_REFUNDED,
            default => null,
        };
    }

    /** Short label for the admin table ("QRIS", "BCA VA", ...). Column is varchar(30). */
    public function methodLabel(array $payload): ?string
    {
        $type = $payload['payment_type'] ?? null;

        $label = match ($type) {
            null => null,
            'qris' => 'QRIS',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'dana' => 'DANA',
            'ovo' => 'OVO',
            'credit_card' => 'Credit Card',
            'echannel' => 'Mandiri VA',
            'bank_transfer' => isset($payload['permata_va_number'])
                ? 'Permata VA'
                : (isset($payload['va_numbers'][0]['bank']) ? Str::upper($payload['va_numbers'][0]['bank']) . ' VA' : 'Bank Transfer'),
            'cstore' => Str::title($payload['store'] ?? 'Convenience Store'),
            default => Str::headline($type),
        };

        return $label === null ? null : Str::limit($label, 30, '');
    }

    /**
     * Applies a (signature-verified) Midtrans notification. Safe to call repeatedly:
     * Midtrans retries, and notifications can arrive out of order.
     */
    public function handleNotification(array $payload): string
    {
        $newStatus = $this->mapStatus($payload);

        if ($newStatus === null) {
            return 'ignored';
        }

        return DB::transaction(function () use ($payload, $newStatus) {
            $transaction = Transaction::where('id_pesanan', $payload['order_id'])->lockForUpdate()->first();

            if ($transaction === null) {
                Log::warning('Midtrans notification for unknown order', ['order_id' => $payload['order_id']]);

                return 'unknown';
            }

            $order = Order::lockForUpdate()->where('id_pesanan', $transaction->id_pesanan)->first();

            if ($order === null) {
                Log::error('Transaction without an order', ['order_id' => $transaction->id_pesanan]);

                return 'unknown';
            }

            if ((int) round((float) $payload['gross_amount']) !== (int) $order->total) {
                Log::error('Midtrans amount mismatch', [
                    'order_id' => $order->id_pesanan,
                    'expected' => $order->total,
                    'got' => $payload['gross_amount'],
                ]);

                return 'amount_mismatch';
            }

            // Same status again (e.g. "pending" once the customer picked GoPay): the status
            // doesn't change, but the payment method / Midtrans ids are new information.
            if ($transaction->status === $newStatus) {
                $transaction->update([
                    'status_midtrans' => Str::limit((string) ($payload['transaction_status'] ?? ''), 30, ''),
                    'id_transaksi_midtrans' => $payload['transaction_id'] ?? $transaction->id_transaksi_midtrans,
                    'metode_pembayaran' => $this->methodLabel($payload) ?? $transaction->metode_pembayaran,
                ]);

                return 'updated';
            }

            if (! $this->canMove($transaction->status, $newStatus)) {
                return 'stale';
            }

            $update = [
                'status' => $newStatus,
                'status_midtrans' => Str::limit((string) ($payload['transaction_status'] ?? ''), 30, ''),
                'id_transaksi_midtrans' => $payload['transaction_id'] ?? $transaction->id_transaksi_midtrans,
                'metode_pembayaran' => $this->methodLabel($payload) ?? $transaction->metode_pembayaran,
            ];

            if ($newStatus === Transaction::STATUS_SETTLED && $transaction->waktu_bayar === null) {
                $update['waktu_bayar'] = $this->paidAt($payload);
            }

            $transaction->update($update);

            if (in_array($newStatus, [Transaction::STATUS_EXPIRED, Transaction::STATUS_FAILED, Transaction::STATUS_CANCELLED], true)) {
                // Payment is dead: the order leaves "waiting", which also releases its reservation.
                $this->orders->reject($order->id, 'Payment ' . $newStatus, [Order::STATUS_WAITING]);
            }

            if ($newStatus === Transaction::STATUS_SETTLED) {
                $this->commitStock($order);
            }

            return 'updated';
        });
    }

    /**
     * The payment is in: turn the reservation into a real stock deduction. Runs once, because
     * a transaction can only enter "settled" once (see canMove).
     *
     * If the stock is gone anyway (admin lowered it, or the payment landed after the window
     * closed and someone else took the last pieces) the order is rejected so the admin can refund.
     */
    private function commitStock(Order $order): void
    {
        if ($order->status === Order::STATUS_REJECTED) {
            // Paid after we already cancelled/expired it. Nothing to auto-fix; admin must refund.
            Log::critical('Payment settled for an already rejected order', ['order_id' => $order->id_pesanan]);

            return;
        }

        if ($order->status !== Order::STATUS_WAITING) {
            return;
        }

        $items = $order->items()->whereNotNull('menu_item_id')->get();

        $menuItems = MenuItem::query()
            ->whereIn('id', $items->pluck('menu_item_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $short = $items->contains(function ($item) use ($menuItems) {
            $menuItem = $menuItems->get($item->menu_item_id);

            return $menuItem === null || $menuItem->stock < $item->quantity;
        });

        if ($short) {
            $order->update([
                'status' => Order::STATUS_REJECTED,
                'rejection_reason' => 'Out of stock after payment. Refund required.',
            ]);

            Log::critical('Payment settled but stock ran out: refund required', ['order_id' => $order->id_pesanan]);

            return;
        }

        foreach ($items as $item) {
            $menuItems[$item->menu_item_id]->decrement('stock', $item->quantity);
        }
    }

    /** Forward-only state machine so a late "pending" never overwrites "settled". */
    private function canMove(string $from, string $to): bool
    {
        if ($from === $to) {
            return false;
        }

        return match ($from) {
            Transaction::STATUS_PENDING => true,
            Transaction::STATUS_SETTLED => $to === Transaction::STATUS_REFUNDED,
            Transaction::STATUS_REFUNDED => false,
            // expired / failed / cancelled: only a real payment can still revive it
            default => $to === Transaction::STATUS_SETTLED,
        };
    }

    private function paidAt(array $payload)
    {
        if (! empty($payload['settlement_time'])) {
            // Midtrans sends WIB (UTC+7) timestamps.
            return \Carbon\Carbon::parse($payload['settlement_time'], 'Asia/Jakarta')->utc();
        }

        return now();
    }
}