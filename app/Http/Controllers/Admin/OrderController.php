<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const PER_PAGE = 10;

    private const SESSION_KEY = 'admin_order_overrides';

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

        $orders = $this->orders();

        if ($status !== 'all' && isset(self::STATUS_LABELS[$status])) {
            $orders = $orders->where('status', $status);
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $orders = $orders->filter(fn (array $order) => str_contains(
                mb_strtolower($order['code'] . ' ' . $order['customer'] . ' ' . $order['items_label']),
                $needle
            ));
        }

        $orders = match ($sort) {
            'oldest' => $orders->sortBy('id'),
            'customer' => $orders->sortBy('customer', SORT_NATURAL | SORT_FLAG_CASE),
            'total_high' => $orders->sortByDesc('total'),
            'total_low' => $orders->sortBy('total'),
            default => $orders->sortByDesc('id'),
        };
        $orders = $orders->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $orders->forPage($page, self::PER_PAGE)->values(),
            $orders->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Re-open the popup that failed validation
        $retryOrder = $request->old('_order_id')
            ? $this->orders()->firstWhere('id', (int) $request->old('_order_id'))
            : null;

        return view('admin.order-index', [
            'orders' => $paginator,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'statusLabels' => self::STATUS_LABELS,
            'retryOrder' => $retryOrder,
            'retryPopup' => $request->old('_popup'),
        ]);
    }

    /**
     * Popup 1: accept or reject a waiting order.
     * Accept -> "process". Reject -> "rejected" (reason required).
     */
    public function confirm(Request $request, int $order): RedirectResponse
    {
        $current = $this->findOrFail($order);

        if ($current['status'] !== 'waiting') {
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
            $this->saveOverride($order, ['status' => 'process', 'rejection_reason' => null]);

            return back()->with('status', "Order {$current['code']} accepted and moved to Process.");
        }

        $this->saveOverride($order, [
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('status', "Order {$current['code']} has been rejected.");
    }

    /**
     * Popup 2: move an accepted order through process -> shipped -> done.
     */
    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        $current = $this->findOrFail($order);

        if (in_array($current['status'], ['waiting', 'rejected'], true)) {
            return back()->withErrors(['order' => 'This order must be accepted before its status can be updated.']);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:process,shipped,done'],
        ], [
            'status.required' => 'Please select a new status.',
        ]);

        $this->saveOverride($order, ['status' => $validated['status']]);

        $label = self::STATUS_LABELS[$validated['status']];

        return back()->with('status', "Order {$current['code']} status updated to {$label}.");
    }

    /* ------------------------------------------------------------------
     | Temporary data layer.
     | Orders are hardcoded until the orders table exists. Status changes
     | made in the popups are kept in the session so the flow can be
     | demonstrated. Replace orders(), findOrFail() and saveOverride()
     | with Eloquent queries / updates later.
     ------------------------------------------------------------------ */

    private function orders(): Collection
    {
        $overrides = session(self::SESSION_KEY, []);

        return $this->seed()->map(function (array $order) use ($overrides) {
            if (isset($overrides[$order['id']])) {
                $order = array_merge($order, $overrides[$order['id']]);
            }

            return $order;
        });
    }

    private function findOrFail(int $id): array
    {
        $order = $this->orders()->firstWhere('id', $id);

        abort_if($order === null, 404);

        return $order;
    }

    private function saveOverride(int $id, array $data): void
    {
        $overrides = session(self::SESSION_KEY, []);
        $overrides[$id] = array_merge($overrides[$id] ?? [], $data);

        session([self::SESSION_KEY => $overrides]);
    }

    private function seed(): Collection
    {
        $p = [
            'original' => ['Original Glaze', 12000],
            'choco' => ['Choco Melt', 15000],
            'strawberry' => ['Strawberry Dream', 15000],
            'vanilla' => ['Vanilla Bean', 14000],
            'boston' => ['Boston Cream', 16000],
            'honey' => ['Honey Lemon', 14000],
            'cinnamon' => ['Cinnamon Sugar', 13000],
            'matcha' => ['Zen Matcha', 18000],
            'caramel' => ['Salted Caramel', 18000],
            'hibiscus' => ['Hibiscus Bloom', 18000],
            'midnight' => ['Midnight Glaze', 17500],
            'cheese' => ['Golden Cheese', 19000],
            'family' => ['Family Box Package', 20000],
            'party' => ['Combo Party Box 12 + 2 Iced Coffee', 68000],
        ];

        // id, customer, delivery, status, [[product key, qty], ...], rejection reason
        $rows = [
            [1, 'Antonio', 'same-day', 'done', [['choco', 4], ['vanilla', 2]]],
            [2, 'Rina Wulandari', 'pre-order', 'done', [['family', 2]]],
            [3, 'Budi Santoso', 'same-day', 'done', [['original', 6]]],
            [4, 'Dewi Lestari', 'same-day', 'done', [['matcha', 2], ['caramel', 2]]],
            [5, 'Keysha Marsha', 'pre-order', 'done', [['party', 1]]],
            [6, 'Farhan Aditya', 'same-day', 'rejected', [['hibiscus', 3]], 'Hibiscus Bloom is sold out for today.'],
            [7, 'Siti Nurhaliza', 'same-day', 'done', [['boston', 3], ['cinnamon', 3]]],
            [8, 'Rizky Pratama', 'pre-order', 'done', [['family', 1], ['matcha', 2]]],
            [9, 'Nadia Putri', 'same-day', 'done', [['strawberry', 4]]],
            [10, 'Hendra Wijaya', 'same-day', 'done', [['midnight', 2], ['cheese', 2]]],
            [11, 'Antonio', 'same-day', 'done', [['honey', 5]]],
            [12, 'Maya Anggraini', 'pre-order', 'shipped', [['party', 1], ['family', 1]]],
            [13, 'Galih Permana', 'same-day', 'shipped', [['choco', 3], ['original', 3]]],
            [14, 'Lestari Ningrum', 'same-day', 'shipped', [['caramel', 4]]],
            [15, 'Dimas Saputra', 'pre-order', 'process', [['family', 3]]],
            [16, 'Putri Ayu', 'same-day', 'process', [['vanilla', 2], ['boston', 2]]],
            [17, 'Yoga Firmansyah', 'same-day', 'process', [['cheese', 3], ['matcha', 1]]],
            [18, 'Anisa Rahma', 'pre-order', 'rejected', [['party', 2]], 'Pre-order slot for that date is already full.'],
            [19, 'Keysha Marsha', 'same-day', 'waiting', [['family', 1], ['matcha', 2]]],
            [20, 'Bagas Nugroho', 'same-day', 'waiting', [['original', 4], ['cinnamon', 2]]],
            [21, 'Citra Kirana', 'pre-order', 'waiting', [['party', 1]]],
            [22, 'Antonio', 'same-day', 'waiting', [['choco', 4], ['vanilla', 2]]],
            [23, 'Vina Melati', 'same-day', 'waiting', [['strawberry', 2], ['honey', 2], ['caramel', 2]]],
            [24, 'Reza Mahendra', 'pre-order', 'waiting', [['midnight', 4]]],
        ];

        $shipping = ['same-day' => 5000, 'pre-order' => 10000];
        $deliveryLabels = ['same-day' => 'Same-day', 'pre-order' => 'Pre-order'];

        return collect($rows)->map(function (array $row) use ($p, $shipping, $deliveryLabels) {
            [$id, $customer, $delivery, $status, $lines] = $row;

            $items = array_map(fn (array $line) => [
                'name' => $p[$line[0]][0],
                'qty' => $line[1],
                'price' => $p[$line[0]][1],
            ], $lines);

            $subtotal = array_sum(array_map(fn (array $i) => $i['price'] * $i['qty'], $items));

            return [
                'id' => $id,
                'code' => '#DW-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                'customer' => $customer,
                'items_label' => implode(', ', array_map(fn (array $i) => "{$i['name']} ({$i['qty']})", $items)),
                'items_popup' => implode(' • ', array_map(fn (array $i) => "{$i['name']} ({$i['qty']}x)", $items)),
                'delivery' => $deliveryLabels[$delivery],
                'total' => $subtotal + $shipping[$delivery],
                'status' => $status,
                'rejection_reason' => $row[5] ?? null,
            ];
        });
    }
}