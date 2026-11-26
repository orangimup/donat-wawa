<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Refund;
use App\Models\Review;
use App\Services\OrderService;
use App\Services\RefundService;
use App\Services\ReviewService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
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
    /** Tabs that map straight onto order statuses. 'review' and 'refund' are filtered separately (see index()). */
    public const STATUS_GROUPS = [
        'processing' => ['waiting', 'process'],
        'shipped' => ['shipped'],
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
        'other' => 'Lainnya',
    ];

    public function index(Request $request): View
    {
        $group = $request->get('status', 'all');
        $all = $this->orders($request->user()->id);

        $inGroup = fn (string $key) => match ($key) {
            'review' => $all->filter(fn (array $order) => $order['can_review']),
            'refund' => $all->filter(fn (array $order) => $order['refund_status'] !== null),
            default => $all->whereIn('status', self::STATUS_GROUPS[$key]),
        };

        $counts = ['all' => $all->count()];

        foreach (['processing', 'shipped', 'completed', 'review', 'cancelled', 'refund'] as $key) {
            $counts[$key] = $inGroup($key)->count();
        }

        $orders = $group !== 'all' && isset($counts[$group]) ? $inGroup($group) : $all;

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
    public function complete(Request $request, OrderService $orders, ReviewService $reviews, int $order): RedirectResponse
    {
        $found = Order::query()->where('user_id', $request->user()->id)->find($order);

        abort_if($found === null, 404);

        if (! $orders->markDone($order, $request->user()->id)) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => __('Only an order that is on its way can be marked as received.')]);
        }

        $model = $this->findModelFor($request->user()->id, $order);

        if ($model !== null && $reviews->canReview($model) && $reviews->reviewableItems($model)->isNotEmpty()) {
            return redirect()
                ->route('settings.orders.review', $order)
                ->with('success', __('Thank you! Your order is complete. How was it? Leave a rating below.'));
        }

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Thank you! Your order is complete. You can now review the products or request a refund within :hours hours.', ['hours' => RefundService::CUSTOMER_WINDOW_HOURS]));
    }

    public function cancel(Request $request, OrderService $orders, RefundService $refunds, int $order): RedirectResponse
    {
        $found = $this->findFor($request->user()->id, $order);

        abort_if($found === null, 404);

        $cancelled = $orders->reject($order, 'Cancelled by customer', [Order::STATUS_WAITING]);

        if ($cancelled === null) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => __('This order can no longer be cancelled.')]);
        }

        $message = __('Your order has been cancelled.');

        $refund = $refunds->refundRejectedOrder($cancelled);

        if ($refund !== null) {
            $message .= ' ' . match (true) {
                $refunds->isManual($refund) => __('Please enter your bank account so we can transfer your refund.'),
                $refund->status === Refund::STATUS_REFUNDED => __('Your payment has been refunded.'),
                default => __('Your refund is being processed.'),
            };
        }

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', $message);
    }
    /** A new refund request (buyer asks within 24 hours after the order is done). */
    public function refund(Request $request, RefundService $refunds, int $order): View|RedirectResponse
    {
        $model = $this->findModelFor($request->user()->id, $order);

        abort_if($model === null, 404);

        // A refund already exists and only needs the bank account: that has its own page.
        if ($this->canEditBank($refunds, $model)) {
            return redirect()->route('settings.orders.refund.bank', $order);
        }

        if (($block = $this->refundBlock($refunds, $model)) !== null) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => $refunds->blockMessage($block)]);
        }

        return view('user.settings.order-refund', [
            'order' => $this->present($model),
            'statusLabels' => self::STATUS_LABELS,
            'refund' => null,
            'reasons' => self::REFUND_REASONS,
            'deadline' => $refunds->customerDeadline($model)->copy()->timezone(Order::TIMEZONE)->format('d M Y, H:i'),
            'needsBank' => $refunds->orderNeedsBankDetails($model),
        ]);
    }

    public function storeRefund(Request $request, RefundService $refunds, int $order): RedirectResponse
    {
        $model = $this->findModelFor($request->user()->id, $order);

        abort_if($model === null, 404);

        $block = $this->refundBlock($refunds, $model);

        if ($block !== null) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => $refunds->blockMessage($block)]);
        }

        $needsBank = $refunds->orderNeedsBankDetails($model);

        $validated = $request->validate([
            'reason' => ['required', Rule::in(array_keys(self::REFUND_REASONS))],
            'details' => ['required', 'string', 'max:1000'],
            'evidence' => ['nullable', 'image', 'max:5120'],
        ] + ($needsBank ? RefundService::bankRules() : []));

        $evidence = $request->file('evidence')?->store('refund-evidence', 'public');

        $refund = $refunds->request(
            $model,
            $validated['reason'],
            $validated['details'],
            $evidence,
            $needsBank ? Arr::only($validated, ['bank_name', 'account_number', 'account_holder_name']) : null,
        );

        if ($refund === null) {
            if ($evidence !== null) {
                Storage::disk('public')->delete($evidence);
            }

            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => $refunds->blockMessage('exists')]);
        }

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Pengajuan refund berhasil dikirim.'));
    }

    /** Bank account form for a refund that is paid back by hand (bank VA payments). */
    public function bank(Request $request, RefundService $refunds, int $order): View|RedirectResponse
    {
        $model = $this->findModelFor($request->user()->id, $order);

        abort_if($model === null, 404);

        if (! $this->canEditBank($refunds, $model)) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => __('There is no refund waiting for bank details on this order.')]);
        }

        return view('user.settings.order-refund-bank', [
            'order' => $this->present($model),
            'statusLabels' => self::STATUS_LABELS,
            'refund' => $model->refund,
            'reasons' => self::REFUND_REASONS,
        ]);
    }

    public function storeBank(Request $request, RefundService $refunds, int $order): RedirectResponse
    {
        $model = $this->findModelFor($request->user()->id, $order);

        abort_if($model === null, 404);

        if (! $this->canEditBank($refunds, $model)) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => __('There is no refund waiting for bank details on this order.')]);
        }

        $validated = $request->validate(RefundService::bankRules());

        if (! $refunds->provideBankDetails($model->refund, $validated)) {
            return redirect()
                ->route('settings.orders.show', $order)
                ->withErrors(['order' => __('There is no refund waiting for bank details on this order.')]);
        }

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Bank details saved. We will transfer your refund soon.'));
    }

    private function refundBlock(RefundService $refunds, Order $order): ?string
    {
        return $order->refund !== null ? 'exists' : $refunds->customerBlockReason($order);
    }

    private function orders(int $userId): Collection
    {
        return Order::query()
            ->with(['items.menuItem', 'items.review', 'transaction', 'refund'])
            ->where('user_id', $userId)
            ->latest('id')
            ->get()
            ->map(fn (Order $order) => $this->present($order));
    }

    private function canEditBank(RefundService $refunds, Order $order): bool
    {
        return $order->refund !== null
            && $order->refund->isPending()
            && $order->refund->asal_refund !== Refund::SOURCE_MIDTRANS
            && $refunds->isManual($order->refund);
    }

    private function findFor(int $userId, int $orderId): ?array
    {
        $order = $this->findModelFor($userId, $orderId);

        return $order ? $this->present($order) : null;
    }

    private function findModelFor(int $userId, int $orderId): ?Order
    {
        return Order::query()
            ->with(['items.menuItem', 'items.review', 'transaction', 'refund'])
            ->where('user_id', $userId)
            ->find($orderId);
    }

    /**
     * Logo (public/assets/payment/*.svg) for a stored label such as "BCA VA" or "QRIS (GoPay)".
     * The wallet that actually paid wins over the generic QRIS mark. null = no logo, show text only.
     */
    private function paymentLogo(?string $label): ?string
    {
        if (blank($label)) {
            return null;
        }

        // Only the methods enabled in config/midtrans.php (gopay, dana, ovo, shopeepay, bca_va, echannel) + generic QRIS
        $known = ['bca', 'mandiri', 'qris', 'gopay', 'shopeepay', 'dana', 'ovo'];

        foreach (array_reverse(preg_split('/[^a-z0-9]+/', strtolower($label), -1, PREG_SPLIT_NO_EMPTY)) as $word) {
            if (in_array($word, $known, true) && file_exists(public_path("assets/payment/{$word}.svg"))) {
                return asset("assets/payment/{$word}.svg");
            }
        }

        return null;
    }

    private function present(Order $order): array
    {
        $refundService = app(RefundService::class);
        $canRefund = $refundService->customerBlockReason($order) === null;

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
            'paid' => (bool) $order->transaction?->isPaid(),
            'method' => $order->transaction?->metode_pembayaran ?? '-',
            'method_logo' => $this->paymentLogo($order->transaction?->metode_pembayaran),
            'txn' => $order->transaction?->id_transaksi_midtrans,
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
            'can_complete' => $order->status === Order::STATUS_SHIPPED,
            // Only after the customer has confirmed receipt: while shipped, the single action is "Pesanan Diterima",
            // which sends them to the review form. If they leave that form, the review action takes its place.
            'can_review' => $order->status === Order::STATUS_DONE
                && app(ReviewService::class)->reviewableItems($order)->isNotEmpty(),
            'paid_at' => $order->transaction?->waktu_bayar?->copy()->timezone(Order::TIMEZONE),
            'confirmed_at' => $order->confirmed_at?->copy()->timezone(Order::TIMEZONE),
            'shipped_at' => $order->shipped_at?->copy()->timezone(Order::TIMEZONE),
            'completed_at' => $order->completed_at?->copy()->timezone(Order::TIMEZONE),
            'can_refund' => $canRefund,
            'refund_deadline' => $canRefund
                ? $refundService->customerDeadline($order)?->copy()->timezone(Order::TIMEZONE)->format('d M Y, H:i')
                : null,
            'refund_status' => $order->refund?->status,
            'refund' => $order->refund ? [
                'status' => $order->refund->status,
                'source' => $order->refund->asal_refund,
                'amount' => $order->refund->jumlah_refund,
                'notes' => $order->refund->catatan_admin,
                'requested_at' => $order->refund->tgl_pengajuan?->copy()->timezone(Order::TIMEZONE),
                'processed_at' => $order->refund->tgl_diproses?->copy()->timezone(Order::TIMEZONE),
            ] : null,
            'refund_needs_bank' => $order->refund !== null
                && $order->refund->isPending()
                && $order->refund->asal_refund !== Refund::SOURCE_MIDTRANS
                && ! $order->refund->hasBankDetails()
                && $refundService->isManual($order->refund),
        ];
    }
}