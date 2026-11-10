<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
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

    public function confirm(Request $request, OrderService $orders, int $order): RedirectResponse
    {
        $current = Order::findOrFail($order);

        if ($current->status !== Order::STATUS_WAITING) {
            return back()->withErrors(['order' => 'Only orders with Waiting status can be confirmed.']);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject'],
            'rejection_reason' => ['nullable', 'string', 'max:500', 'required_if:action,reject'],
        ], [
            'action.required' => 'Please choose to accept or reject the order.',
            'rejection_reason.required_if' => 'Please enter a reason for rejecting the order.',
        ]);

        if ($validated['action'] === 'accept') {
            $accepted = Order::whereKey($order)
                ->where('status', Order::STATUS_WAITING)
                ->update(['status' => Order::STATUS_PROCESS, 'rejection_reason' => null]);

            if ($accepted === 0) {
                return back()->withErrors(['order' => 'This order was already updated. Please refresh the page.']);
            }

            return back()->with('status', "Order {$current->display_code} accepted and moved to Process.");
        }

        $rejected = $orders->reject($order, $validated['rejection_reason'], [Order::STATUS_WAITING]);

        if ($rejected === null) {
            return back()->withErrors(['order' => 'This order was already updated. Please refresh the page.']);
        }

        return back()->with('status', "Order {$current->display_code} has been rejected.");
    }

    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        $current = Order::findOrFail($order);

        if (!in_array($current->status, Order::FLOW, true)) {
            return back()->withErrors(['order' => 'This order must be accepted before its status can be updated.']);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', Order::FLOW)],
        ], [
            'status.required' => 'Please select a new status.',
        ]);

        $from = array_search($current->status, Order::FLOW, true);
        $to = array_search($validated['status'], Order::FLOW, true);

        if ($to < $from) {
            return back()->withErrors(['status' => 'An order cannot be moved back to an earlier status.'])
                ->withInput();
        }

        $current->update(['status' => $validated['status']]);

        $label = self::STATUS_LABELS[$validated['status']];

        return back()->with('status', "Order {$current->display_code} status updated to {$label}.");
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