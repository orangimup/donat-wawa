<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TransactionController extends Controller
{
    private const PER_PAGE = 10;

    public const STATUS_LABELS = [
        'settled' => 'Settled',
        'pending' => 'Pending',
        'expired' => 'Expired',
        'failed' => 'Failed',
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

        $transactions = $this->transactions();

        if ($status !== 'all' && isset(self::STATUS_LABELS[$status])) {
            $transactions = $transactions->where('status', $status);
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $transactions = $transactions->filter(fn (array $trx) => str_contains(
                mb_strtolower($trx['code'] . ' ' . $trx['customer'] . ' ' . $trx['method']),
                $needle
            ));
        }

        $transactions = match ($sort) {
            'oldest' => $transactions->sortBy('timestamp'),
            'customer' => $transactions->sortBy('customer', SORT_NATURAL | SORT_FLAG_CASE),
            'amount_high' => $transactions->sortByDesc('amount'),
            'amount_low' => $transactions->sortBy('amount'),
            default => $transactions->sortByDesc('timestamp'),
        };
        $transactions = $transactions->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $transactions->forPage($page, self::PER_PAGE)->values(),
            $transactions->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.transaction-index', [
            'transactions' => $paginator,
            'stats' => $this->stats(),
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    /* ------------------------------------------------------------------
     | Temporary data layer.
     | Everything below is hardcoded until the orders/transactions tables
     | exist and Midtrans is connected (next PBL). Replace stats() with
     | aggregate queries and transactions() with an Eloquent query that
     | maps the business status (settled / pending / expired / failed)
     | from Midtrans's transaction_status.
     ------------------------------------------------------------------ */

    private function stats(): array
    {
        return [
            ['title' => 'Total Revenue', 'value' => '12M', 'badge' => '+12%', 'caption' => 'this month', 'featured' => true, 'icon' => 'trend'],
            ['title' => 'Pending Payments', 'value' => '23', 'badge' => null, 'caption' => 'right-now', 'featured' => false, 'icon' => 'cart'],
            ['title' => 'Successful Payments', 'value' => '890', 'badge' => null, 'caption' => 'this month', 'featured' => false, 'icon' => 'cart'],
            ['title' => 'Failed Payments', 'value' => '25', 'badge' => null, 'caption' => 'this month', 'featured' => false, 'icon' => 'cart'],
        ];
    }

    private function transactions(): Collection
    {
        // id, customer, amount, method, status, date & time (WIB)
        $rows = [
            [1, 'Keysha Marsha', 28000, 'QRIS', 'settled', '2026-09-01 15:40:04'],
            [2, 'Antonio', 28000, 'QRIS', 'settled', '2026-09-02 15:40:04'],
            [3, 'Keysha Marsha', 28000, 'QRIS', 'settled', '2026-09-03 15:40:04'],
            [4, 'Antonio', 28000, 'QRIS', 'settled', '2026-09-04 15:40:04'],
            [5, 'Rina Wulandari', 50000, 'GoPay', 'settled', '2026-09-05 09:12:37'],
            [6, 'Budi Santoso', 77000, 'BCA VA', 'settled', '2026-09-05 13:26:51'],
            [7, 'Dewi Lestari', 77000, 'QRIS', 'settled', '2026-09-06 10:03:18'],
            [8, 'Farhan Aditya', 59000, 'ShopeePay', 'expired', '2026-09-07 11:45:02'],
            [9, 'Siti Nurhaliza', 58000, 'QRIS', 'settled', '2026-09-08 16:20:44'],
            [10, 'Rizky Pratama', 71000, 'BNI VA', 'settled', '2026-09-09 08:55:29'],
            [11, 'Nadia Putri', 65000, 'GoPay', 'settled', '2026-09-10 14:31:10'],
            [12, 'Hendra Wijaya', 75000, 'QRIS', 'failed', '2026-09-11 12:08:47'],
            [13, 'Antonio', 75000, 'QRIS', 'settled', '2026-09-12 17:42:33'],
            [14, 'Maya Anggraini', 88000, 'Mandiri VA', 'settled', '2026-09-13 09:18:05'],
            [15, 'Galih Permana', 100000, 'QRIS', 'settled', '2026-09-14 15:07:52'],
            [16, 'Lestari Ningrum', 77000, 'ShopeePay', 'expired', '2026-09-15 10:36:21'],
            [17, 'Dimas Saputra', 70000, 'QRIS', 'settled', '2026-09-16 13:49:16'],
            [18, 'Putri Ayu', 65000, 'GoPay', 'settled', '2026-09-17 08:22:40'],
            [19, 'Keysha Marsha', 61000, 'QRIS', 'settled', '2026-09-18 11:14:58'],
            [20, 'Bagas Nugroho', 79000, 'BCA VA', 'failed', '2026-09-18 16:03:27'],
            [21, 'Citra Kirana', 78000, 'QRIS', 'pending', '2026-09-19 09:41:13'],
            [22, 'Antonio', 93000, 'GoPay', 'pending', '2026-09-19 14:27:36'],
            [23, 'Vina Melati', 99000, 'QRIS', 'pending', '2026-09-20 08:05:49'],
            [24, 'Reza Mahendra', 80000, 'BNI VA', 'expired', '2026-09-20 10:52:02'],
        ];

        return collect($rows)->map(function (array $row) {
            [$id, $customer, $amount, $method, $status, $datetime] = $row;

            $date = Carbon::parse($datetime, 'Asia/Jakarta');

            return [
                'id' => $id,
                'code' => '#TRX-98234-' . chr(64 + $id),
                'customer' => $customer,
                'amount' => $amount,
                'method' => $method,
                'status' => $status,
                'timestamp' => $date->timestamp,
                'date_label' => $date->format('d M Y') . ',',
                'time_label' => $date->format('H:i:s') . ' WIB',
            ];
        });
    }
}