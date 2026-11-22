<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments, RefundService $refunds): JsonResponse
    {
        $payload = $request->json()->all() ?: $request->all();

        if (! $payments->hasValidSignature($payload)) {
            Log::warning('Midtrans notification with invalid signature', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $result = $payments->handleNotification($payload);

        // A paid order that ended up rejected (stock ran out, payment arrived late) is refunded now,
        // after the notification has been saved.
        if ($result === 'updated') {
            $refunds->refundRejectedOrder((string) $payload['order_id']);
        }

        return response()->json(['result' => $result]);
    }
}