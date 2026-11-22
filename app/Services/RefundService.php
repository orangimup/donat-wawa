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

/**
 * Where a refund comes from (Refund::asal_refund):
 *  - pembeli:  the buyer asks within 24 hours after the order is done, an admin approves.
 *  - sistem:   a PAID order ends up rejected (admin rejects it, customer cancels it, stock ran
 *              out, payment arrived late). No request is needed.
 *  - midtrans: someone refunded in the Midtrans dashboard; the webhook only gets recorded.
 *
 * How the money goes back depends on how the order was paid:
 *  - e-wallet / QRIS / card -> automatic, through the Midtrans refund API.
 *  - bank VA (BCA, Mandiri) -> Midtrans cannot refund these, so an admin transfers the money by
 *    hand to the account the customer gave, uploads the proof and marks it refunded.
 *
 * Midtrans is never called inside a database transaction: callers run these methods after their
 * own transaction has committed.
 */
class RefundService
{
    public const CUSTOMER_WINDOW_HOURS = 24;

    /**
     * Payment method labels (Transaction::metode_pembayaran, see PaymentService::methodLabel) that the
     * Midtrans refund API can pay back. Everything else, bank VA included, is refunded manually.
     */
    public const AUTO_REFUND_METHODS = ['GoPay', 'ShopeePay', 'DANA', 'OVO', 'QRIS', 'Credit Card'];

    public function __construct(private readonly MidtransRefundGateway $gateway)
    {
    }

    /** What the buyer gets back. Change here if shipping should not be refunded. */
    public function refundAmount(Order $order): int
    {
        return (int) $order->total;
    }

    /** Validation rules for the bank details form (field names match the form inputs). */
    public static function bankRules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'regex:/^[0-9]{5,20}$/'],
            'account_holder_name' => ['required', 'string', 'max:100'],
        ];
    }

    /* ------------------------------------------------------------------
     | Automatic or manual?
     ------------------------------------------------------------------ */

    public function isAutomatic(?Transaction $transaction): bool
    {
        return in_array($transaction?->metode_pembayaran, self::AUTO_REFUND_METHODS, true);
    }

    /** True when the customer has to give a bank account for this order's refund. */
    public function orderNeedsBankDetails(Order $order): bool
    {
        $order->loadMissing('transaction');

        return ! $this->isAutomatic($order->transaction);
    }

    public function isManual(Refund $refund): bool
    {
        $refund->loadMissing('order.transaction');

        return ! $this->isAutomatic($refund->order?->transaction);
    }

    /* ------------------------------------------------------------------
     | Customer requests
     ------------------------------------------------------------------ */

    public function customerDeadline(Order $order): ?CarbonInterface
    {
        if ($order->status !== Order::STATUS_DONE) {
            return null;
        }

        // Orders finished before completed_at existed fall back to their last update.
        $doneAt = $order->completed_at ?? $order->updated_at;

        return $doneAt?->copy()->addHours(self::CUSTOMER_WINDOW_HOURS);
    }

    /** null = the customer may ask for a refund; otherwise a short code saying why not. */
    public function customerBlockReason(Order $order): ?string
    {
        $order->loadMissing(['transaction', 'refund']);

        if ($order->status !== Order::STATUS_DONE) {
            return 'not_done';
        }

        if ($order->refund !== null) {
            return 'exists';
        }

        if (! $order->transaction?->isPaid()) {
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

    /**
     * @param  array{bank_name: string, account_number: string, account_holder_name: string}|null  $bank  required when the order needs a manual refund
     * @return Refund|null null when another request for the same order won the race
     */
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

    /**
     * Saves (or corrects) the bank account of a refund that is paid by hand. Allowed until the admin
     * has transferred the money.
     */
    public function provideBankDetails(Refund $refund, array $bank, array $extra = []): bool
    {
        if (! $refund->isPending() || $refund->asal_refund === Refund::SOURCE_MIDTRANS || ! $this->isManual($refund)) {
            return false;
        }

        // Reason, description and evidence come from the same form; only overwrite what was filled in.
        $extra = array_filter(
            Arr::only($extra, ['alasan', 'detail_alasan', 'foto_bukti']),
            fn ($value) => filled($value),
        );

        $refund->update($this->bankColumns($bank) + $extra);

        return true;
    }

    /** @return array<string, string|null> */
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

    /* ------------------------------------------------------------------
     | Admin decisions
     ------------------------------------------------------------------ */

    /**
     * Approve an automatic refund = pay it back through Midtrans right now. Also used to retry a failed
     * one. Manual (bank) refunds are not touched here: see completeManually().
     */
    public function approve(Refund $refund, ?string $notes = null): Refund
    {
        $refund = $refund->fresh();

        if (! $refund->isPending()) {
            return $refund;
        }

        if (filled($notes)) {
            $refund->update(['catatan_admin' => $notes]);
        }

        return $this->process($refund);
    }

    /** Only customer requests can be rejected; automatic refunds are owed to the buyer. */
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

    /**
     * The admin transferred the money by hand: store the proof and close the refund.
     * Returns false when it is no longer pending or the customer's bank details are missing.
     */
    public function completeManually(Refund $refund, string $proofPath, ?string $notes = null): bool
    {
        if (! $this->isManual($refund)) {
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

    /* ------------------------------------------------------------------
     | Refund of a paid order that was rejected
     ------------------------------------------------------------------ */

    /**
     * Safe to call any number of times: it does nothing unless the order is rejected, its payment
     * is settled and no refund exists yet. A refund that already exists (even a failed one) is
     * left for an admin to retry, so repeated webhooks cannot loop.
     *
     * Paid by e-wallet: refunded through Midtrans immediately.
     * Paid by bank: a pending refund is created and waits for the customer's bank details.
     */
    public function refundRejectedOrder(Order|string $order): ?Refund
    {
        try {
            $order = $order instanceof Order
                ? $order->fresh(['transaction', 'refund'])
                : Order::with(['transaction', 'refund'])->where('id_pesanan', $order)->first();

            if (
                $order === null
                || $order->status !== Order::STATUS_REJECTED
                || ! $order->transaction?->isPaid()
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
            return null; // another request created it first
        } catch (Throwable $e) {
            Log::error('Automatic refund could not be started', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /* ------------------------------------------------------------------
     | Refund made directly in Midtrans
     ------------------------------------------------------------------ */

    /** Called for a refund / partial_refund notification. Only writes to our own database. */
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
                // Our own refund call created it a moment ago; nothing to record.
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

    /* ------------------------------------------------------------------
     | Talking to Midtrans (automatic refunds only)
     ------------------------------------------------------------------ */

    public function process(Refund $refund): Refund
    {
        // Bank payments cannot be refunded through Midtrans; an admin transfers them by hand.
        if (! $refund->isPending() || $refund->order === null || $this->isManual($refund)) {
            return $refund;
        }

        try {
            // A previous attempt may have gone through without us hearing back (timeout).
            // Ask Midtrans first, so a retry can never pay the customer twice.
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
