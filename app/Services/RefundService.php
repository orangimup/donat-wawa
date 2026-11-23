<?php

namespace App\Services;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RefundService
{
    public const CUSTOMER_WINDOW_HOURS = 24;
    public const AUTO_REFUND_METHODS = ['GoPay', 'ShopeePay', 'DANA', 'OVO', 'QRIS', 'Credit Card'];

    public function __construct(private readonly MidtransRefundGateway $gateway)
    {
    }

    public function refundAmount(Order $order): int
    {
        return (int) $order->total;
    }

    public static function bankRules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'regex:/^[0-9]{5,20}$/'],
            'account_holder_name' => ['required', 'string', 'max:100'],
        ];
    }
    public function isAutomatic(?Transaction $transaction): bool
    {
        $method = (string) $transaction?->metode_pembayaran;

        return in_array($method, self::AUTO_REFUND_METHODS, true) || str_starts_with($method, 'QRIS');
    }
    public function orderNeedsBankDetails(Order $order): bool
    {
        $order->loadMissing('transaction');

        return !$this->isAutomatic($order->transaction);
    }

    public function isManual(Refund $refund): bool
    {
        $refund->loadMissing('order.transaction');

        return !$this->isAutomatic($refund->order?->transaction);
    }
    public function customerDeadline(Order $order): ?CarbonInterface
    {
        if ($order->status !== Order::STATUS_DONE) {
            return null;
        }

        $doneAt = $order->completed_at ?? $order->updated_at;

        return $doneAt?->copy()->addHours(self::CUSTOMER_WINDOW_HOURS);
    }
    public function customerBlockReason(Order $order): ?string
    {
        $order->loadMissing(['transaction', 'refund']);

        if ($order->status !== Order::STATUS_DONE) {
            return 'not_done';
        }

        if ($order->refund !== null) {
            return 'exists';
        }

        if (!$order->transaction?->isPaid()) {
            return 'not_paid';
        }

        $deadline = $this->customerDeadline($order);

        if ($deadline === null || now()->greaterThan($deadline)) {
            return 'expired';
        }

        return null;
    }

    public function blockMessage(string $reason): string
    {
        return match ($reason) {
            'exists' => __('A refund request already exists for this order.'),
            'expired' => __('The refund window for this order has closed. Refunds can only be requested within :hours hours after the order is completed.', ['hours' => self::CUSTOMER_WINDOW_HOURS]),
            'not_paid' => __('This order has no completed payment, so it cannot be refunded.'),
            default => __('Refunds can only be requested for completed orders.'),
        };
    }
    public function request(Order $order, string $reason, string $details, ?string $evidencePath, ?array $bank = null): ?Refund
    {
        abort_unless($this->customerBlockReason($order) === null, 403);
        abort_if($this->orderNeedsBankDetails($order) && $bank === null, 422);

        try {
            return Refund::create([
                'id_pesanan' => $order->id_pesanan,
                'asal_refund' => Refund::SOURCE_CUSTOMER,
                'alasan' => $reason,
                'detail_alasan' => $details,
                'foto_bukti' => $evidencePath,
                'jumlah_refund' => $this->refundAmount($order),
                'status' => Refund::STATUS_PENDING,
                'tgl_pengajuan' => now(),
            ] + $this->bankColumns($bank));
        } catch (UniqueConstraintViolationException) {
            return null;
        }
    }
    public function provideBankDetails(Refund $refund, array $bank, array $extra = []): bool
    {
        if (!$refund->isPending() || $refund->asal_refund === Refund::SOURCE_MIDTRANS || !$this->isManual($refund)) {
            return false;
        }

        $extra = array_filter(
            Arr::only($extra, ['alasan', 'detail_alasan', 'foto_bukti']),
            fn($value) => filled($value),
        );

        $refund->update($this->bankColumns($bank) + $extra);

        return true;
    }
    private function bankColumns(?array $bank): array
    {
        if ($bank === null) {
            return [];
        }

        return [
            'nama_bank' => $bank['bank_name'],
            'nomor_rekening' => $bank['account_number'],
            'nama_pemilik_rekening' => $bank['account_holder_name'],
        ];
    }
    public function approve(Refund $refund, ?string $notes = null): Refund
    {
        $refund = $refund->fresh();

        if (!$refund->isPending()) {
            return $refund;
        }

        if (filled($notes)) {
            $refund->update(['catatan_admin' => $notes]);
        }

        return $this->process($refund);
    }
    public function reject(Refund $refund, string $notes): bool
    {
        return Refund::query()
            ->whereKey($refund->id)
            ->where('status', Refund::STATUS_PENDING)
            ->where('asal_refund', Refund::SOURCE_CUSTOMER)
            ->update([
                'status' => Refund::STATUS_REJECTED,
                'catatan_admin' => $notes,
                'tgl_diproses' => now(),
            ]) === 1;
    }
    public function completeManually(Refund $refund, string $proofPath, ?string $notes = null): bool
    {
        if (!$this->isManual($refund)) {
            return false;
        }

        $changes = [
            'status' => Refund::STATUS_REFUNDED,
            'bukti_transfer' => $proofPath,
            'tgl_diproses' => now(),
            'pesan_error' => null,
        ];

        if (filled($notes)) {
            $changes['catatan_admin'] = $notes;
        }

        $done = Refund::query()
            ->whereKey($refund->id)
            ->where('status', Refund::STATUS_PENDING)
            ->whereNotNull('nomor_rekening')
            ->whereNotNull('nama_bank')
            ->whereNotNull('nama_pemilik_rekening')
            ->update($changes) === 1;

        if ($done) {
            $this->markTransactionRefunded($refund->id_pesanan);
        }

        return $done;
    }
    public function refundRejectedOrder(Order|string $order): ?Refund
    {
        try {
            $order = $order instanceof Order
                ? $order->fresh(['transaction', 'refund'])
                : Order::with(['transaction', 'refund'])->where('id_pesanan', $order)->first();

            if (
                $order === null
                || $order->status !== Order::STATUS_REJECTED
                || !$order->transaction?->isPaid()
                || $order->refund !== null
            ) {
                return null;
            }

            $refund = Refund::create([
                'id_pesanan' => $order->id_pesanan,
                'asal_refund' => Refund::SOURCE_SYSTEM,
                'alasan' => 'order_rejected',
                'detail_alasan' => $order->rejection_reason,
                'jumlah_refund' => $this->refundAmount($order),
                'status' => Refund::STATUS_PENDING,
                'tgl_pengajuan' => now(),
            ]);

            return $this->process($refund);
        } catch (UniqueConstraintViolationException) {
            return null;
        } catch (Throwable $e) {
            Log::error('Automatic refund could not be started', ['error' => $e->getMessage()]);

            return null;
        }
    }
    public function recordFromWebhook(Order $order, array $payload): void
    {
        $refund = Refund::where('id_pesanan', $order->id_pesanan)->first();

        if ($refund === null) {
            try {
                Refund::create([
                    'id_pesanan' => $order->id_pesanan,
                    'asal_refund' => Refund::SOURCE_MIDTRANS,
                    'alasan' => 'midtrans_direct',
                    'jumlah_refund' => (int) round((float) ($payload['refund_amount'] ?? $order->total)),
                    'id_refund_midtrans' => isset($payload['refund_key']) ? (string) $payload['refund_key'] : null,
                    'status' => Refund::STATUS_REFUNDED,
                    'tgl_pengajuan' => now(),
                    'tgl_diproses' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
            }

            return;
        }

        // A request was still open but the money was already returned from the Midtrans side.
        if ($refund->isPending()) {
            $refund->update([
                'status' => Refund::STATUS_REFUNDED,
                'tgl_diproses' => now(),
                'pesan_error' => null,
                'catatan_admin' => $refund->catatan_admin ?? 'Refunded directly via Midtrans.',
            ]);
        }
    }
    public function process(Refund $refund): Refund
    {
        if (!$refund->isPending() || $refund->order === null || $this->isManual($refund)) {
            return $refund;
        }

        try {
            if ($refund->jumlah_percobaan > 0 && $this->alreadyRefundedAtMidtrans($refund->id_pesanan)) {
                return $this->markRefunded($refund, null);
            }

            $refund->increment('jumlah_percobaan');

            $response = $this->gateway->refund(
                $refund->id_pesanan,
                (int) $refund->jumlah_refund,
                $this->midtransReason($refund),
                'RF-' . $refund->id_pesanan . '-' . $refund->jumlah_percobaan,
            );
        } catch (Throwable $e) {
            return $this->markFailed($refund, $e->getMessage());
        }

        if ((int) ($response['status_code'] ?? 0) !== 200) {
            return $this->markFailed(
                $refund,
                ($response['status_message'] ?? 'Unexpected response from Midtrans') . ' (code ' . ($response['status_code'] ?? '?') . ')'
            );
        }

        return $this->markRefunded($refund, (string) ($response['refund_chargeback_id'] ?? $response['refund_key'] ?? ''));
    }

    private function alreadyRefundedAtMidtrans(string $idPesanan): bool
    {
        $status = $this->gateway->status($idPesanan);

        return in_array($status['transaction_status'] ?? null, ['refund', 'partial_refund'], true);
    }

    private function markRefunded(Refund $refund, ?string $midtransRefundId): Refund
    {
        $refund->update([
            'status' => Refund::STATUS_REFUNDED,
            'tgl_diproses' => now(),
            'pesan_error' => null,
            'id_refund_midtrans' => filled($midtransRefundId) ? $midtransRefundId : $refund->id_refund_midtrans,
        ]);

        // The webhook will say the same thing a moment later; doing it now keeps the admin
        // pages right even where the webhook cannot reach us (localhost).
        $this->markTransactionRefunded($refund->id_pesanan);

        return $refund;
    }

    private function markTransactionRefunded(string $idPesanan): void
    {
        Transaction::query()
            ->where('id_pesanan', $idPesanan)
            ->where('status', Transaction::STATUS_SETTLED)
            ->update(['status' => Transaction::STATUS_REFUNDED, 'status_midtrans' => 'refund']);
    }

    private function markFailed(Refund $refund, string $message): Refund
    {
        Log::error('Midtrans refund failed', ['order_id' => $refund->id_pesanan, 'error' => $message]);

        $refund->update(['pesan_error' => Str::limit($message, 1000, '')]);

        return $refund;
    }

    private function midtransReason(Refund $refund): string
    {
        return $refund->asal_refund === Refund::SOURCE_SYSTEM
            ? 'Pesanan ditolak oleh toko'
            : 'Refund pesanan ' . $refund->id_pesanan;
    }
}