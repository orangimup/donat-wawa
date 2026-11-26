<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    /** How many months the Revenue Trend card shows. */
    private const TREND_MONTHS = 6;

    /** How far back the monthly recap can be shifted. */
    private const HISTORY_MONTHS = 12;

    private const STATUS_LABELS = [
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

    public function index(): View
    {
        $tz = Order::TIMEZONE;
        $now = now($tz);
        $historyStart = $now->copy()->startOfMonth()->subMonthsNoOverflow(self::HISTORY_MONTHS - 1);

        // Orders that count = everything except rejected (rejected also covers unpaid / expired ones).
        // Rows are grouped in PHP with the Jakarta timezone so the result is the same on MySQL and SQLite.
        $orderTimes = Order::query()
            ->where('status', '!=', Order::STATUS_REJECTED)
            ->where('created_at', '>=', $historyStart->copy()->utc())
            ->get(['id', 'created_at'])
            ->map(fn (Order $order) => $order->created_at->copy()->timezone($tz));

        // Revenue = settled payments only (refunded money is not revenue), by payment time.
        $payments = Transaction::query()
            ->join('orders', 'orders.id_pesanan', '=', 'transactions.id_pesanan')
            ->where('transactions.status', Transaction::STATUS_SETTLED)
            ->where('transactions.waktu_bayar', '>=', $historyStart->copy()->utc())
            ->get(['transactions.waktu_bayar', 'orders.total'])
            ->map(fn (Transaction $trx) => [
                'at' => $trx->waktu_bayar->copy()->timezone($tz),
                'amount' => (int) $trx->total,
            ]);

        $ordersByDay = $orderTimes->countBy(fn ($time) => $time->format('Y-m-d'));
        $ordersByMonth = $orderTimes->countBy(fn ($time) => $time->format('Y-m'));
        $revenueByMonth = $payments->groupBy(fn ($p) => $p['at']->format('Y-m'))->map(fn ($g) => (int) $g->sum('amount'));

        $todayKey = $now->format('Y-m-d');
        $yesterdayKey = $now->copy()->subDay()->format('Y-m-d');
        $thisMonthKey = $now->format('Y-m');
        $lastMonthKey = $now->copy()->startOfMonth()->subMonthNoOverflow()->format('Y-m');

        $lowStock = MenuItem::query()
            ->where('status', 'active')
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->orderBy('stock')
            ->orderBy('name')
            ->get(['name', 'stock']);

        $stats = [
            [
                'title' => 'Daily Orders',
                'value' => (string) ($ordersByDay[$todayKey] ?? 0),
                'badge' => $this->change($ordersByDay[$todayKey] ?? 0, $ordersByDay[$yesterdayKey] ?? 0),
                'caption' => 'vs yesterday',
                'featured' => true,
            ],
            [
                'title' => 'Total Orders',
                'value' => (string) ($ordersByMonth[$thisMonthKey] ?? 0),
                'badge' => $this->change($ordersByMonth[$thisMonthKey] ?? 0, $ordersByMonth[$lastMonthKey] ?? 0),
                'caption' => 'this month',
                'featured' => false,
            ],
            [
                'title' => 'Pending Orders',
                'value' => (string) Order::where('status', Order::STATUS_WAITING)->count(),
                'badge' => null,
                'caption' => 'Needs immediate attention',
                'featured' => false,
            ],
            [
                'title' => 'Low Stock Items',
                'value' => (string) $lowStock->count(),
                'badge' => null,
                'caption' => $lowStock->isEmpty()
                    ? 'All products are well stocked'
                    : $lowStock->pluck('name')->implode(', '),
                'featured' => false,
            ],
        ];

        // ---- Chart data. The month picker chooses the month; the tabs choose the granularity:
        //      daily   = one bar per day of that month
        //      weekly  = one bar per week of that month (days 1-7, 8-14, ...)
        //      monthly = one bar per month over the last 12 months (the chosen month is highlighted)
        $firstTimestamp = $orderTimes->map(fn ($time) => $time->timestamp)->min();
        $firstMonth = $firstTimestamp
            ? \Illuminate\Support\Carbon::createFromTimestamp($firstTimestamp, $tz)->startOfMonth()
            : $now->copy()->startOfMonth();

        $months = [];
        for ($cursor = $firstMonth->copy(); $cursor->lte($now); $cursor->addMonthNoOverflow()) {
            $key = $cursor->format('Y-m');
            $short = $cursor->translatedFormat('M');

            $dayValues = [];
            $dayLabels = [];
            $dayTitles = [];
            for ($day = 1; $day <= $cursor->daysInMonth; $day++) {
                $dayValues[] = (int) ($ordersByDay[sprintf('%s-%02d', $key, $day)] ?? 0);
                $dayLabels[] = (string) $day;
                $dayTitles[] = $day . ' ' . $short;
            }

            $weekValues = [];
            $weekLabels = [];
            $weekTitles = [];
            foreach (array_chunk($dayValues, 7) as $i => $chunk) {
                $from = $i * 7 + 1;
                $to = $from + count($chunk) - 1;
                $weekValues[] = array_sum($chunk);
                $weekLabels[] = __('Week :n', ['n' => $i + 1]);
                $weekTitles[] = ($from === $to ? $from : $from . '-' . $to) . ' ' . $short;
            }

            $months[] = [
                'key' => $key,
                'period' => $cursor->translatedFormat('F Y'),
                'daily' => ['labels' => $dayLabels, 'titles' => $dayTitles, 'values' => $dayValues],
                'weekly' => ['labels' => $weekLabels, 'titles' => $weekTitles, 'values' => $weekValues],
            ];
        }

        $overview = ['keys' => [], 'labels' => [], 'titles' => [], 'values' => []];
        for ($back = self::HISTORY_MONTHS - 1; $back >= 0; $back--) {
            $month = $now->copy()->startOfMonth()->subMonthsNoOverflow($back);
            $overview['keys'][] = $month->format('Y-m');
            $overview['labels'][] = $month->translatedFormat('M');
            $overview['titles'][] = $month->translatedFormat('F Y');
            $overview['values'][] = (int) ($ordersByMonth[$month->format('Y-m')] ?? 0);
        }

        $chart = [
            'months' => $months,
            'overview' => $overview,
        ];

        // ---- Revenue trend: last N months, with real amounts ----
        $trendMonths = [];
        $trendValues = [];
        for ($back = self::TREND_MONTHS - 1; $back >= 0; $back--) {
            $month = $now->copy()->startOfMonth()->subMonthsNoOverflow($back);
            $trendMonths[] = $month;
            $trendValues[] = (int) ($revenueByMonth[$month->format('Y-m')] ?? 0);
        }

        $trend = $this->trendGeometry($trendValues);
        $lastIndex = self::TREND_MONTHS - 1;

        $trend['points'] = array_map(
            fn (array $point, int $i) => $point + [
                'month' => $trendMonths[$i]->translatedFormat('M'),
                'period' => $trendMonths[$i]->translatedFormat('F Y'),
                'amount_label' => $this->rupiah($trendValues[$i]),
                'compact' => $this->compactRupiah($trendValues[$i]),
                'current' => $i === $lastIndex,
            ],
            $trend['points'],
            array_keys($trend['points'])
        );
        $trend['total_label'] = $this->rupiah($trendValues[$lastIndex]);
        $trend['change'] = $this->change($trendValues[$lastIndex], $trendValues[$lastIndex - 1], 1);

        return view('admin.dashboard', [
            'stats' => $stats,
            'chart' => $chart,
            'trend' => $trend,
            'topProduct' => $this->topProduct(),
            'latestOrders' => $this->latestOrders(),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    /** Percentage change as "+12%" / "-3.5%", or null when there is nothing to compare with. */
    private function change(int|float $current, int|float $previous, int $decimals = 0): ?string
    {
        if ($previous <= 0) {
            return null;
        }

        $percent = round((($current - $previous) / $previous) * 100, $decimals) + 0.0;

        return sprintf('%+.' . $decimals . 'f%%', $percent);
    }

    private function rupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /** Short amount for chart labels: 1,2 jt / 250 rb (id) or 1.2M / 250K (en). */
    private function compactRupiah(int $amount): string
    {
        $id = app()->getLocale() === 'id';
        $point = $id ? ',' : '.';
        $trim = fn (float $n) => rtrim(rtrim(number_format($n, 1, $point, ''), '0'), $point);

        return match (true) {
            $amount >= 1_000_000_000 => $trim($amount / 1_000_000_000) . ($id ? ' M' : 'B'),
            $amount >= 1_000_000 => $trim($amount / 1_000_000) . ($id ? ' jt' : 'M'),
            $amount >= 1_000 => $trim($amount / 1_000) . ($id ? ' rb' : 'K'),
            default => (string) $amount,
        };
    }

    /**
     * SVG paths for the trend line plus each point's position in percent,
     * so the dots on the card line up exactly with the curve.
     */
    private function trendGeometry(array $values): array
    {
        $count = count($values);
        $min = min($values);
        $max = max($values);
        $range = max($max - $min, 1);

        $points = [];
        foreach ($values as $i => $value) {
            $points[] = [
                round($i * 300 / ($count - 1), 2),
                round(88 - (($value - $min) / $range) * 66, 2),
            ];
        }

        $line = "M {$points[0][0]} {$points[0][1]}";
        for ($i = 1; $i < $count; $i++) {
            [$x0, $y0] = $points[$i - 1];
            [$x1, $y1] = $points[$i];
            $mid = round(($x0 + $x1) / 2, 2);
            $line .= " C {$mid} {$y0}, {$mid} {$y1}, {$x1} {$y1}";
        }

        return [
            'line' => $line,
            'area' => $line . ' L 300 100 L 0 100 Z',
            'points' => array_map(
                fn (array $p) => ['x' => round($p[0] / 3, 2), 'y' => $p[1]],
                $points
            ),
        ];
    }

    /** Best-selling product by quantity (all non-rejected orders), or null when nothing has sold yet. */
    private function topProduct(): ?array
    {
        $row = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', Order::STATUS_REJECTED)
            ->whereNotNull('order_items.menu_item_id')
            ->selectRaw('order_items.menu_item_id as menu_item_id, SUM(order_items.quantity) as sold')
            ->groupBy('order_items.menu_item_id')
            ->orderByDesc('sold')
            ->orderBy('order_items.menu_item_id')
            ->first();

        $menuItem = $row ? MenuItem::find($row->menu_item_id) : null;

        if (! $menuItem) {
            return null;
        }

        return [
            'name' => $menuItem->name,
            'sold' => (int) $row->sold,
            'image' => $menuItem->image_url ?? asset('assets/images/donat.png'),
            'url' => route('admin.menu-items.index', ['search' => $menuItem->name]),
        ];
    }

    private function latestOrders(): array
    {
        return Order::query()
            ->with('items')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'code' => $order->display_code,
                'customer' => $order->customer_name,
                'items' => $order->items->map(fn ($item) => $item->name . ' (' . $item->quantity . ')')->implode(', '),
                'total' => $order->total,
                'delivery' => $order->delivery_label,
                'status' => $order->status,
            ])
            ->all();
    }
}
