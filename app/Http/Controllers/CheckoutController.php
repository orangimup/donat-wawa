<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\ShippingException;
use App\Services\ShippingService;
use App\Services\RefundService;
use Carbon\Carbon;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('user.checkout', [
            'deliverySlots' => Order::DELIVERY_SLOTS,
            'earliestPreOrderDate' => Carbon::tomorrow(Order::TIMEZONE)->toDateString(),
        ]);
    }

    public function syncBasket(Request $request): JsonResponse
    {
        $slugs = array_filter((array) $request->query('slugs', []));

        $items = MenuItem::query()
            ->whereIn('slug', $slugs)
            ->get()
            ->map(fn(MenuItem $item) => [
                'slug' => $item->slug,
                'name' => $item->name,
                'price' => $item->price,
                'image' => $item->image_url,
                'available' => $item->is_available,
            ])
            ->keyBy('slug');

        return response()->json(['items' => $items]);
    }
    public function suggestAddress(Request $request, ShippingService $shipping): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        try {
            return response()->json(['suggestions' => $shipping->suggest($data['q'])]);
        } catch (ShippingException $e) {
            return response()->json(['message' => $e->getMessage(), 'reason' => $e->reason], $e->status);
        }
    }
    public function shippingQuote(Request $request, ShippingService $shipping): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            return response()->json($shipping->quoteByCoordinates((float) $data['lat'], (float) $data['lng']));
        } catch (ShippingException $e) {
            return response()->json(['message' => $e->getMessage(), 'reason' => $e->reason], $e->status);
        }
    }

    public function store(Request $request, OrderService $orders, ShippingService $shipping): JsonResponse
    {
        $earliest = Carbon::tomorrow(Order::TIMEZONE)->toDateString();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s-]{8,15}$/'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'address_area' => ['required', 'string', 'max:255'],
            'address_detail' => ['required', 'string', 'max:200'],
            'delivery_type' => ['required', Rule::in(array_keys(Order::DELIVERY_LABELS))],
            'delivery_date' => ['required_if:delivery_type,pre_order', 'nullable', 'date', 'after_or_equal:' . $earliest],
            'delivery_slot' => ['required_if:delivery_type,pre_order', 'nullable', Rule::in(Order::DELIVERY_SLOTS)],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.slug' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        try {
            $quote = $shipping->quoteByCoordinates((float) $data['lat'], (float) $data['lng']);
        } catch (ShippingException $e) {
            return response()->json(['message' => $e->getMessage(), 'reason' => $e->reason], $e->status);
        }

        // Alamat lengkap = detail dari pelanggan + area dari peta + link pin untuk kurir.
        $quote['address'] = trim($data['address_detail']) . ', ' . trim($data['address_area'])
            . "\nPin: https://www.google.com/maps?q=" . round((float) $data['lat'], 6) . ',' . round((float) $data['lng'], 6);

        $order = $orders->place($request->user(), $data, $data['items'], $quote);

        return response()->json([
            'order_code' => $order->id_pesanan,
            'subtotal' => $order->subtotal,
            'shipping_fee' => $order->shipping_fee,
            'total' => $order->total,
        ], 201);
    }
    public function createSnapToken(Request $request, PaymentService $payments): JsonResponse
    {
        $request->validate(['order_code' => ['required', 'string']]);

        $order = Order::with(['items', 'transaction'])
            ->where('id_pesanan', $request->input('order_code'))
            ->where('user_id', $request->user()->id)
            ->where('status', Order::STATUS_WAITING)
            ->firstOrFail();

        $existing = $order->transaction;

        if ($existing?->isPaid()) {
            return response()->json(['message' => __('This order has already been paid.')], 409);
        }

        if ($existing?->isPending()
            && $existing->snap_token
            && $existing->batas_waktu_bayar?->isFuture()) {
            return response()->json(['snap_token' => $existing->snap_token]);
        }

        $payments->configureMidtrans();

        $expiryMinutes = (int) config('midtrans.expiry_minutes', 60);

        $itemDetails = $order->items
            ->map(fn($item) => [
                'id' => (string) ($item->menu_item_id ?? $item->id),
                'price' => $item->price,
                'quantity' => $item->quantity,
                'name' => Str::limit($item->name, 50, ''),
            ])
            ->all();

        $itemDetails[] = [
            'id' => 'SHIPPING',
            'price' => $order->shipping_fee,
            'quantity' => 1,
            'name' => 'Shipping fee',
        ];

        $params = [
            'transaction_details' => [
                'order_id' => $order->id_pesanan,
                'gross_amount' => $order->total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ],
            'enabled_payments' => config('midtrans.enabled_payments'),
            'expiry' => ['unit' => 'minute', 'duration' => $expiryMinutes],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans snap token failed', ['order' => $order->id_pesanan, 'error' => $e->getMessage()]);

            return response()->json(['message' => __('Something went wrong, please try again.')], 502);
        }

        Transaction::updateOrCreate(
            ['id_pesanan' => $order->id_pesanan],
            [
                'snap_token' => $snapToken,
                'status' => Transaction::STATUS_PENDING,
                'batas_waktu_bayar' => now()->addMinutes($expiryMinutes),
            ],
        );

        return response()->json(['snap_token' => $snapToken]);
    }
    public function syncPayment(Request $request, PaymentService $payments, RefundService $refunds, string $code): JsonResponse
    {
        $order = Order::with('transaction')
            ->where('id_pesanan', $code)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $payments->configureMidtrans();

        try {
            $status = json_decode(json_encode(\Midtrans\Transaction::status($order->id_pesanan)), true);
        } catch (\Throwable $e) {
            return $this->paymentState($order, $order->transaction?->status ?? Transaction::STATUS_PENDING);
        }

        $payments->handleNotification($status);
        $refunds->refundRejectedOrder($order);

        return $this->paymentState($order, $order->transaction()->value('status'));
    }
    private function paymentState(Order $order, ?string $status): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'paid' => $status === Transaction::STATUS_SETTLED,
            'order_url' => route('settings.orders.show', $order->id),
        ]);
    }

    public function success(): View
    {
        return view('user.order-confirmed');
    }
}