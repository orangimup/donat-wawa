<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Services\OrderService;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderHistoryController extends Controller
{
    private const PER_PAGE = 4;

    public const STATUS_LABELS = [
        'waiting' => 'Waiting',
        'process' => 'Process',
        'shipped' => 'Shipped',
        'done' => 'Completed',
        'rejected' => 'Cancelled',
    ];
    public const STATUS_GROUPS = [
        'pending' => ['waiting', 'process', 'shipped'],
        'completed' => ['done'],
        'cancelled' => ['rejected'],
    ];

    public const REVIEW_LABELS = [
        1 => 'Sangat mengecewakan, tidak sesuai harapan',
        2 => 'Kurang memuaskan, banyak yang perlu diperbaiki',
        3 => 'Cukup baik, tapi masih bisa lebih baik',
        4 => 'Enak dan memuaskan!',
        5 => 'Luar biasa lezat! Pasti pesan lagi',
    ];

    public const REFUND_REASONS = [
        'damaged' => 'Barang rusak/cacat',
        'wrong_item' => 'Pesanan tidak sesuai',
        'not_delivered' => 'Pesanan tidak sampai',
        'changed_mind' => 'Berubah pikiran',
        'other' => 'Lainnya',
    ];

    public function index(Request $request): View
    {
        $group = $request->get('status', 'all');
        $all = $this->orders($request->user()->id);

        $counts = [
            'all' => $all->count(),
            'pending' => $all->whereIn('status', self::STATUS_GROUPS['pending'])->count(),
            'completed' => $all->whereIn('status', self::STATUS_GROUPS['completed'])->count(),
            'cancelled' => $all->whereIn('status', self::STATUS_GROUPS['cancelled'])->count(),
        ];

        $orders = $group !== 'all' && isset(self::STATUS_GROUPS[$group])
            ? $all->whereIn('status', self::STATUS_GROUPS[$group])
            : $all;

        $orders = $orders->sortByDesc(fn (array $order) => $order['placed_at'])->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $orders->forPage($page, self::PER_PAGE)->values(),
            $orders->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $latest = $all->sortByDesc(fn (array $order) => $order['placed_at'])->first();
        $lastUpdated = $latest
            ? ($latest['placed_at']->isToday()
                ? __('Today') . ' ' . $latest['placed_at']->format('H:i')
                : $latest['placed_at']->format('d M Y, H:i'))
            : '-';

        return view('user.settings.order-history', [
            'orders' => $paginator,
            'group' => $group,
            'counts' => $counts,
            'statusLabels' => self::STATUS_LABELS,
            'lastUpdated' => $lastUpdated,
        ]);
    }

    public function show(Request $request, int $order): View
    {
        $found = $this->findFor($request->user()->id, $order);

        abort_if($found === null, 404);

        return view('user.settings.order-detail', [
            'order' => $found,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function review(Request $request, ReviewService $reviews, int $order): View|RedirectResponse
    {
        $model = $this->findModelFor($request->user()->id, $order);

        abort_if($model === null, 404);
        abort_unless($reviews->canReview($model), 403);

        $reviewItems = $reviews->reviewableItems($model);

        if ($reviewItems->isEmpty()) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->with('success', __('Semua produk di pesanan ini sudah kamu ulas.'));
        }

        return view('user.settings.order-review', [
            'order' => $this->present($model),
            'reviewItems' => $reviewItems->map(fn (OrderItem $line) => [
                'id' => $line->id,
                'name' => $line->name,
                'image' => $line->menuItem?->image_url ?? asset('assets/images/donat.png'),
            ]),
            'reviewLabels' => self::REVIEW_LABELS,
        ]);
    }

    public function storeReview(Request $request, ReviewService $reviews, int $order): RedirectResponse
    {
        $model = $this->findModelFor($request->user()->id, $order);

        abort_if($model === null, 404);
        abort_unless($reviews->canReview($model), 403);

        $validated = $request->validate([
            'reviews' => ['required', 'array'],
            // Keys are order_items.id. A line without a rating is simply skipped.
            'reviews.*.rating' => ['nullable', 'integer', 'between:' . Review::MIN_RATING . ',' . Review::MAX_RATING],
            'reviews.*.comment' => ['nullable', 'string', 'max:' . Review::COMMENT_MAX_LENGTH],
        ]);

        if ($reviews->reviewableItems($model)->isEmpty()) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => __('Semua produk di pesanan ini sudah kamu ulas.')]);
        }

        $created = $reviews->submit($request->user(), $model, $validated['reviews']);

        if ($created === 0) {
            return back()
                ->withInput()
                ->withErrors(['reviews' => __('Pilih rating untuk minimal satu produk.')]);
        }

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Terima kasih atas ulasanmu!'));
    }

    public function cancel(Request $request, OrderService $orders, int $order): RedirectResponse
    {
        $found = $this->findFor($request->user()->id, $order);

        abort_if($found === null, 404);

        $cancelled = $orders->reject($order, 'Cancelled by customer', [Order::STATUS_WAITING]);

        if ($cancelled === null) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => __('This order can no longer be cancelled.')]);
        }

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Your order has been cancelled.'));
    }

    public function refund(Request $request, int $order): View
    {
        $found = $this->findFor($request->user()->id, $order);

        abort_if($found === null, 404);
        abort_unless(in_array($found['status'], ['done', 'rejected'], true), 403);

        return view('user.settings.order-refund', [
            'order' => $found,
            'statusLabels' => self::STATUS_LABELS,
            'reasons' => self::REFUND_REASONS,
        ]);
    }

    public function storeRefund(Request $request, int $order): RedirectResponse
    {
        $found = $this->findFor($request->user()->id, $order);

        abort_if($found === null, 404);
        abort_unless(in_array($found['status'], ['done', 'rejected'], true), 403);

        $validated = $request->validate([
            'reason' => ['required', Rule::in(array_keys(self::REFUND_REASONS))],
            'details' => ['required', 'string', 'max:1000'],
            'evidence' => ['nullable', 'image', 'max:5120'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:100'],
        ]);

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Pengajuan refund berhasil dikirim.'));
    }

    private function orders(int $userId): Collection
    {
        return Order::query()
            ->with(['items.menuItem', 'items.review'])
            ->where('user_id', $userId)
            ->latest('id')
            ->get()
            ->map(fn (Order $order) => $this->present($order));
    }

    private function findFor(int $userId, int $orderId): ?array
    {
        $order = $this->findModelFor($userId, $orderId);

        return $order ? $this->present($order) : null;
    }

    private function findModelFor(int $userId, int $orderId): ?Order
    {
        return Order::query()
            ->with(['items.menuItem', 'items.review'])
            ->where('user_id', $userId)
            ->find($orderId);
    }

    private function present(Order $order): array
    {
        $items = $order->items->map(fn (OrderItem $line) => [
            'name' => $line->name,
            'image' => $line->menuItem?->image_url ?? asset('assets/images/donat.png'),
            'price' => $line->price,
            'qty' => $line->quantity,
        ]);

        return [
            'id' => $order->id,
            'code' => $order->display_code,
            'placed_at' => $order->placed_at,
            'status' => $order->status,
            'method' => '-',
            'txn' => null,
            'items' => $items,
            'items_label' => $items->map(fn (array $i) => $i['name'] . ' (' . $i['qty'] . ')')->implode(', '),
            'qty_total' => $items->sum('qty'),
            'subtotal' => $order->subtotal,
            'shipping_fee' => $order->shipping_fee,
            'total' => $order->total,
            'address_label' => 'Delivery Address',
            'address_lines' => $order->address,
            'address_note' => null,
            'rejection_reason' => $order->rejection_reason,
            'can_cancel' => $order->canBeCancelledByCustomer(),
            'can_review' => app(ReviewService::class)->reviewableItems($order)->isNotEmpty(),
        ];
    }
}