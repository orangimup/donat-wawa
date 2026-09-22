<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Carbon\Carbon;
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
        $found = $this->orders($request->user()->id)->firstWhere('id', $order);

        abort_if($found === null, 404);

        return view('user.settings.order-detail', [
            'order' => $found,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function review(Request $request, int $order): View
    {
        $found = $this->orders($request->user()->id)->firstWhere('id', $order);

        abort_if($found === null, 404);
        abort_unless($found['status'] === 'done', 403);

        return view('user.settings.order-review', [
            'order' => $found,
            'reviewLabels' => self::REVIEW_LABELS,
        ]);
    }

    public function storeReview(Request $request, int $order): RedirectResponse
    {
        $found = $this->orders($request->user()->id)->firstWhere('id', $order);

        abort_if($found === null, 404);
        abort_unless($found['status'] === 'done', 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // TODO: simpan ke tabel reviews begitu modelnya ada

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Terima kasih atas ulasanmu!'));
    }

    public function refund(Request $request, int $order): View
    {
        $found = $this->orders($request->user()->id)->firstWhere('id', $order);

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
        $found = $this->orders($request->user()->id)->firstWhere('id', $order);

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

        // TODO: simpan ke tabel refund_requests + upload evidence-nya begitu modelnya ada

        return redirect()
            ->route('settings.orders.show', $order)
            ->with('success', __('Pengajuan refund berhasil dikirim.'));
    }

    private function orders(int $userId): Collection
    {
        $products = MenuItem::all()->keyBy('name');

        $rows = [
            [
                'id' => 1,
                'placed_at' => '2026-09-02 14:20',
                'status' => 'process',
                'method' => 'GoPay QRIS',
                'txn' => 'TRX-88492042',
                'shipping_fee' => 10000,
                'address_label' => 'Home Address',
                'address_lines' => 'Jl. Pahlawan No. 339, Balearjosari, Lowokwaru, Kota Malang, Jawa Timur 65126',
                'address_note' => 'Leave at the security post / by the front door',
                'items' => [['Original Glaze', 2]],
            ],
            [
                'id' => 2,
                'placed_at' => '2026-08-29 10:05',
                'status' => 'shipped',
                'method' => 'Bank Transfer BCA',
                'txn' => 'TRX-88401118',
                'shipping_fee' => 10000,
                'address_label' => 'Home Address',
                'address_lines' => 'Jl. Pahlawan No. 339, Balearjosari, Lowokwaru, Kota Malang, Jawa Timur 65126',
                'address_note' => null,
                'items' => [['Choco with Oreo Crumbs', 3], ['Dark Chocolate Drizzle', 3]],
            ],
            [
                'id' => 3,
                'placed_at' => '2026-08-24 16:45',
                'status' => 'done',
                'method' => 'OVO',
                'txn' => 'TRX-88322091',
                'shipping_fee' => 5000,
                'address_label' => 'Office Address',
                'address_lines' => 'Jl. Soekarno Hatta No. 12, Mojolangu, Lowokwaru, Kota Malang, Jawa Timur 65142',
                'address_note' => 'Leave with the front-desk receptionist',
                'items' => [['Original Glaze', 6]],
            ],
            [
                'id' => 4,
                'placed_at' => '2026-08-20 09:30',
                'status' => 'done',
                'method' => 'GoPay QRIS',
                'txn' => 'TRX-88255034',
                'shipping_fee' => 10000,
                'address_label' => 'Home Address',
                'address_lines' => 'Jl. Pahlawan No. 339, Balearjosari, Lowokwaru, Kota Malang, Jawa Timur 65126',
                'address_note' => null,
                'items' => [['Strawberry Sprinkle', 2], ['Red Velvet', 2]],
            ],
            [
                'id' => 5,
                'placed_at' => '2026-08-14 13:10',
                'status' => 'waiting',
                'method' => 'Cash on Delivery',
                'txn' => null,
                'shipping_fee' => 10000,
                'address_label' => 'Home Address',
                'address_lines' => 'Jl. Pahlawan No. 339, Balearjosari, Lowokwaru, Kota Malang, Jawa Timur 65126',
                'address_note' => null,
                'items' => [['Blueberry Burst', 3], ['Oreo Crumbs', 2]],
            ],
            [
                'id' => 6,
                'placed_at' => '2026-08-09 11:55',
                'status' => 'rejected',
                'method' => 'GoPay QRIS',
                'txn' => 'TRX-88118820',
                'shipping_fee' => 10000,
                'address_label' => 'Home Address',
                'address_lines' => 'Jl. Pahlawan No. 339, Balearjosari, Lowokwaru, Kota Malang, Jawa Timur 65126',
                'address_note' => null,
                'rejection_reason' => 'Blueberry Burst is sold out for today.',
                'items' => [['Blueberry Burst', 3]],
            ],
            [
                'id' => 7,
                'placed_at' => '2026-08-03 15:20',
                'status' => 'done',
                'method' => 'Bank Transfer BCA',
                'txn' => 'TRX-88041207',
                'shipping_fee' => 5000,
                'address_label' => 'Office Address',
                'address_lines' => 'Jl. Soekarno Hatta No. 12, Mojolangu, Lowokwaru, Kota Malang, Jawa Timur 65142',
                'address_note' => null,
                'items' => [['Strawberry Sprinkle', 4]],
            ],
            [
                'id' => 8,
                'placed_at' => '2026-07-28 12:00',
                'status' => 'done',
                'method' => 'OVO',
                'txn' => 'TRX-87910044',
                'shipping_fee' => 10000,
                'address_label' => 'Home Address',
                'address_lines' => 'Jl. Pahlawan No. 339, Balearjosari, Lowokwaru, Kota Malang, Jawa Timur 65126',
                'address_note' => null,
                'items' => [['Dark Chocolate Drizzle', 2], ['Choco with Oreo Crumbs', 2]],
            ],
            [
                'id' => 9,
                'placed_at' => '2026-07-19 14:40',
                'status' => 'process',
                'method' => 'GoPay QRIS',
                'txn' => 'TRX-87765512',
                'shipping_fee' => 10000,
                'address_label' => 'Home Address',
                'address_lines' => 'Jl. Pahlawan No. 339, Balearjosari, Lowokwaru, Kota Malang, Jawa Timur 65126',
                'address_note' => null,
                'items' => [['Red Velvet', 4]],
            ],
        ];

        return collect($rows)->map(function (array $row) use ($products) {
            $items = collect($row['items'])->map(function (array $line) use ($products) {
                $product = $products->get($line[0]);

                return [
                    'name' => $product->name ?? $line[0],
                    'image' => $product?->image_url ?: self::PLACEHOLDER_IMAGE,
                    'price' => (int) ($product->price ?? 0),
                    'qty' => $line[1],
                ];
            });

            $subtotal = $items->sum(fn (array $i) => $i['price'] * $i['qty']);
            $qtyTotal = $items->sum('qty');
            $placedAt = Carbon::parse($row['placed_at']);

            return [
                'id' => $row['id'],
                'code' => '#DW-' . $placedAt->format('Ymd') . '-' . str_pad((string) $row['id'], 3, '0', STR_PAD_LEFT),
                'placed_at' => $placedAt,
                'status' => $row['status'],
                'method' => $row['method'],
                'txn' => $row['txn'],
                'items' => $items,
                'items_label' => $items->map(fn (array $i) => $i['name'] . ' (' . $i['qty'] . ')')->implode(', '),
                'qty_total' => $qtyTotal,
                'subtotal' => $subtotal,
                'shipping_fee' => $row['shipping_fee'],
                'total' => $subtotal + $row['shipping_fee'],
                'address_label' => $row['address_label'],
                'address_lines' => $row['address_lines'],
                'address_note' => $row['address_note'],
                'rejection_reason' => $row['rejection_reason'] ?? null,
            ];
        });
    }
}