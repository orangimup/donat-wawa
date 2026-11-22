<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * The only class that talks to Midtrans about refunds. It exists so tests can replace it
 * with a fake and never hit the network.
 */
class MidtransRefundGateway
{
    /**
     * Asks Midtrans to refund a settled payment. Midtrans answers with status_code 200 on success.
     * Some errors come back as a normal response (check status_code), others are thrown.
     *
     * @return array<string, mixed>
     */
    public function refund(string $orderId, int $amount, string $reason, string $refundKey): array
    {
        $this->configure();

        $response = \Midtrans\Transaction::refund($orderId, [
            // Same key = same refund. This is what stops a retried request from paying twice.
            'refund_key' => $refundKey,
            'amount' => $amount,
            'reason' => Str::limit($reason, 255, ''),
        ]);

        return json_decode(json_encode($response), true) ?? [];
    }

    /** @return array<string, mixed> */
    public function status(string $orderId): array
    {
        $this->configure();

        return json_decode(json_encode(\Midtrans\Transaction::status($orderId)), true) ?? [];
    }

    // Same settings as PaymentService::configureMidtrans(); kept here so the two services
    // do not depend on each other.
    private function configure(): void
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }
}
