<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

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
        $lowStock = MenuItem::query()
            ->where('status', 'active')
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->orderBy('stock')
            ->orderBy('name')
            ->get(['name', 'stock']);

        // Everything else is order data, which is hardcoded until the orders table exists.
        $stats = [
            ['title' => 'Daily Orders', 'value' => '42', 'badge' => '+12%', 'caption' => 'vs yesterday', 'featured' => true],
            ['title' => 'Total Orders', 'value' => '790', 'badge' => '+8%', 'caption' => 'this month', 'featured' => false],
            ['title' => 'Pending Orders', 'value' => '18', 'badge' => null, 'caption' => 'Needs immediate attention', 'featured' => false],
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

        $chart = [
            'today' => [
                'labels' => ['09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'],
                'values' => [3, 6, 9, 10, 7, 4, 3],
            ],
            'weekly' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'values' => [30, 34, 46, 30, 36, 28, 42],
            ],
            'monthly' => [
                'labels' => ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
                'values' => [612, 655, 703, 748, 821, 790],
            ],
        ];

        $trend = $this->trendPaths([12, 14, 20, 34, 48, 54, 56]);
        $trend['change'] = '+8.4%';

        $topProduct = MenuItem::where('name', 'Original Glaze')->first();

        $topProductData = [
            'name' => 'Original Glaze',
            'sold' => 420,
            'image' => $topProduct?->image_url ?? asset('assets/images/donat.png'),
            'url' => route('admin.menu-items.index', ['search' => 'Original Glaze']),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'chart' => $chart,
            'trend' => $trend,
            'topProduct' => $topProductData,
            'latestOrders' => $this->latestOrders(),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    private function trendPaths(array $values): array
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
        ];
    }

    private function latestOrders(): array
    {
        return [
            ['code' => '#DW-0024', 'customer' => 'Reza Mahendra', 'items' => 'Midnight Glaze (4)', 'total' => 80000, 'delivery' => 'Pre-order', 'status' => 'waiting'],
            ['code' => '#DW-0023', 'customer' => 'Vina Melati', 'items' => 'Strawberry Dream (2), Honey Lemon (2), Salted Caramel (2)', 'total' => 99000, 'delivery' => 'Same-day', 'status' => 'waiting'],
            ['code' => '#DW-0022', 'customer' => 'Antonio', 'items' => 'Choco Melt (4), Vanilla Bean (2)', 'total' => 93000, 'delivery' => 'Same-day', 'status' => 'waiting'],
            ['code' => '#DW-0021', 'customer' => 'Citra Kirana', 'items' => 'Combo Party Box 12 + 2 Iced Coffee (1)', 'total' => 78000, 'delivery' => 'Pre-order', 'status' => 'waiting'],
            ['code' => '#DW-0020', 'customer' => 'Bagas Nugroho', 'items' => 'Original Glaze (4), Cinnamon Sugar (2)', 'total' => 79000, 'delivery' => 'Same-day', 'status' => 'waiting'],
        ];
    }
}