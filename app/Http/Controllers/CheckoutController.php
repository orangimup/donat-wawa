<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('user.checkout');
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

    public function createSnapToken(Request $request): JsonResponse
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $items = $request->input('items', []);
        $shipping = (int) $request->input('shipping_fee', 0);

        $grossAmount = collect($items)->sum(fn($i) => $i['price'] * $i['qty']) + $shipping;
        $orderId = 'DW-' . now()->format('Ymd') . '-' . strtoupper(uniqid());

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->nama,
                'email' => auth()->user()->email,
                'phone' => $request->input('phone'),
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return response()->json(['snap_token' => $snapToken]);
    }

    public function success(): View
    {
        return view('user.order-confirmed');
    }
}
