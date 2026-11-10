<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments): JsonResponse
    {
        $payload = $request->json()->all() ?: $request->all();

        if (! $payments->hasValidSignature($payload)) {
            Log::warning('Midtrans notification with invalid signature', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $result = $payments->handleNotification($payload);

        return response()->json(['result' => $result]);
    }
}