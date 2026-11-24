<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Refund;
use App\Services\OrderService;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const PER_PAGE = 10;

    public const STATUS_LABELS = [
        'waiting' => 'Waiting',
        'process' => 'Process',
        'shipped' => 'Shipped',
        'done' => 'Done',
        'rejected' => 'Rejected',
    ];

    public function __construct()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index(Request $request): View
    {
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));
        $sort = $request->get('sort', 'latest');

        $query = Order::query()->with('items');

        if ($status !== 'all' && isset(self::STATUS_LABELS[$status])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $needle = '%' . ltrim($search, '#') . '%';

            $query->where(function ($q) use ($needle) {
                $q->where('id_pesanan', 'like', $needle)
                    ->orWhere('customer_name', 'like', $needle)
                    ->orWhereHas('items', fn($items) => $items->where('name', 'like', $needle));
            });
        }

        match ($sort) {
            'oldest' => $query->orderBy('id'),
            'customer' => $query->orderBy('customer_name'),
            'total_high' => $query->orderByDesc('total')->orderByDesc('id'),
            'total_low' => $query->orderBy('total')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        $paginator = $query
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn(Order $order) => $this->present($order));

        $retryOrder = $request->old('_order_id')
            ? Order::with('items')->find((int) $request->old('_order_id'))
            : null;

        return view('admin.order-index', [
            'orders' => $paginator,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'statusLabels' => self::STATUS_LABELS,
            'retryOrder' => $retryOrder ? $this->present($retryOrder) : null,
            'retryPopup' => $request->old('_popup'),
        ]);
    }
    public function show(int $order): View
    {
        $model = Order::with(['items.menuItem', 'transaction', 'refund'])->findOrFail($order);

        return view('admin.order-show', [
            'order' => $this->presentDetail($model),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function confirm(Request $request, OrderService $orders, RefundService $refunds, int $order): RedirectResponse
    {
        $current = Order::with('transaction')->findOrFail($order);

        if ($current->status !== Order::STATUS_WAITING) {
            return back()->withErrors(['order' => __('Only orders with Waiting status can be confirmed.')]);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
            'rejection_reason' => ['nullable', 'string', 'max:500', 'required_if:action,reject'],
        ], [
            'action.required' => __('Please choose to accept or reject the order.'),
            'rejection_reason.required_if' => __('Please enter a reason for rejecting the order.'),
        ]);

        if ($validated['action'] === 'accept') {
            if (! $current->transaction?->isPaid()) {
                return back()->withErrors(['order' => __('Payment has not been received for this order yet, so it cannot be accepted.')]);
            }

            $accepted = Order::whereKey($order)
                ->where('status', Order::STATUS_WAITING)
                ->update(['status' => Order::STATUS_PROCESS, 'rejection_reason' => null, 'confirmed_at' => now()]);

            if ($accepted === 0) {
                return back()->withErrors(['order' => __('This order was already updated. Please refresh the page.')]);
            }

            return back()->with('status', __('Order :code accepted and moved to Process.', ['code' => $current->display_code]));
        }

        $rejected = $orders->reject($order, $validated['rejection_reason'], [Order::STATUS_WAITING]);

        if ($rejected === null) {
            return back()->withErrors(['order' => __('This order was already updated. Please refresh the page.')]);
        }

        $message = __('Order :code has been rejected.', ['code' => $current->display_code]);

        $refund = $refunds->refundRejectedOrder($rejected);

        if ($refund !== null) {
            if ($refunds->isManual($refund)) {
                $message .= ' ' . __('It was paid by bank transfer, so the customer was asked to provide a bank account. Transfer the refund manually from the Refunds page.');
            } else {
                $message .= $refund->status === Refund::STATUS_REFUNDED
                    ? ' ' . __('The payment has been refunded to the customer automatically.')
                    : ' ' . __('The automatic refund could not be completed. Open Refunds to retry it.');
            }
        }

        return back()->with('status', $message);
    }

    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        $current = Order::findOrFail($order);

        if ($current->status !== Order::STATUS_PROCESS) {
            return back()->withErrors(['order' => match ($current->status) {
                Order::STATUS_SHIPPED => __('This order is on its way. It is completed when the customer confirms they received it.'),
                Order::STATUS_DONE => __('This order has already been completed by the customer.'),
                default => __('This order must be accepted before its status can be updated.'),
            }]);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', Order::ADMIN_FLOW)],
        ], [
            'status.required' => __('Please select a new status.'),
        ]);

        if ($validated['status'] === Order::STATUS_PROCESS) {
            return back()->with('status', __('Order :code is still in process.', ['code' => $current->display_code]));
        }

        $current->update([
            'status' => $validated['status'],
            'shipped_at' => $validated['status'] === Order::STATUS_SHIPPED ? now() : $current->shipped_at,
        ]);

        $label = self::STATUS_LABELS[$validated['status']];

        return back()->with('status', __('Order :code status updated to :status.', ['code' => $current->display_code, 'status' => __($label)]));
    }

    private function presentDetail(Order $order): array
    {
        $transaction = $order->transaction;
        $refund = $order->refund;

        return [
            'id' => $order->id,
            'code' => $order->display_code,
            'status' => $order->status,
            'placed_label' => $order->placed_at?->translatedFormat('d F Y, H:i') . ' WIB',
            'customer' => $order->customer_name,
            'email' => $order->customer_email,
            'phone' => $order->customer_phone,
            'address' => $order->address,
            'distance_km' => $order->distance_km,
            'delivery' => $order->delivery_label,
            'delivery_date' => $order->delivery_date?->translatedFormat('d F Y'),
            'delivery_slot' => $order->delivery_slot,
            'items' => $order->items->map(fn ($line) => [
                'name' => $line->name,
                'image' => $line->menuItem?->image_url ?? asset('assets/images/donat.png'),
                'price' => $line->price,
                'qty' => $line->quantity,
                'line_total' => $line->price * $line->quantity,
            ]),
            'subtotal' => $order->subtotal,
            'shipping_fee' => $order->shipping_fee,
            'total' => $order->total,
            'rejection_reason' => $order->rejection_reason,
            'payment' => [
                'is_paid' => (bool) $transaction?->isPaid(),
                'status' => $transaction?->status,
                'method' => $transaction?->metode_pembayaran ?? '-',
                'midtrans_id' => $transaction?->id_transaksi_midtrans,
                'paid_label' => $transaction?->waktu_bayar
                    ? $transaction->waktu_bayar->copy()->timezone(Order::TIMEZONE)->translatedFormat('d F Y, H:i') . ' WIB'
                    : null,
            ],
            'refund' => $refund ? [
                'id' => $refund->id,
                'status' => $refund->status,
                'source' => Refund::SOURCE_LABELS[$refund->asal_refund] ?? $refund->asal_refund,
                'amount' => $refund->jumlah_refund,
            ] : null,
        ];
    }

    private function present(Order $order): array
    {
        $lines = $order->items;

        return [
            'id' => $order->id,
            'code' => $order->display_code,
            'customer' => $order->customer_name,
            'items_label' => $lines->map(fn($i) => "{$i->name} ({$i->quantity})")->implode(', '),
            'items_popup' => $lines->map(fn($i) => "{$i->name} ({$i->quantity}x)")->implode(' • '),
            'delivery' => $order->delivery_label,
            'total' => $order->total,
            'status' => $order->status,
            'rejection_reason' => $order->rejection_reason,
        ];
    }
}