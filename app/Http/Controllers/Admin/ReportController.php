<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function download(Request $request)
    {
        if (! is_dir(storage_path('fonts'))) {
            mkdir(storage_path('fonts'), 0775, true);
        }

        $tz = Order::TIMEZONE;
        $input = (string) $request->input('month', '');

        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $input)
            ? Carbon::createFromFormat('Y-m-d', $input . '-01', $tz)->startOfMonth()
            : now($tz)->startOfMonth();

        $from = $month->copy()->utc();
        $to = $month->copy()->endOfMonth()->utc();

        $previous = $month->copy()->subMonth()->startOfMonth();
        $previousFrom = $previous->copy()->utc();
        $previousTo = $previous->copy()->endOfMonth()->utc();

        $orders = Order::query()
            ->with(['items', 'transaction'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $valid = $orders->where('status', '!=', Order::STATUS_REJECTED);

        $paid = $this->paidTotals($from, $to);
        $previousPaid = $this->paidTotals($previousFrom, $previousTo);

        $change = $previousPaid['total'] > 0
            ? round(($paid['total'] - $previousPaid['total']) / $previousPaid['total'] * 100, 1)
            : null;

        $products = $valid
            ->flatMap(fn (Order $order) => $order->items)
            ->groupBy('name')
            ->map(fn ($rows, $name) => [
                'name' => $name,
                'quantity' => (int) $rows->sum('quantity'),
                'total' => (int) $rows->sum(fn ($row) => $row->price * $row->quantity),
            ])
            ->sortByDesc('quantity')
            ->values();

        $summary = [
            'orders' => $orders->count(),
            'done' => $orders->where('status', Order::STATUS_DONE)->count(),
            'running' => $orders->whereIn('status', [
                Order::STATUS_WAITING,
                Order::STATUS_PROCESS,
                Order::STATUS_SHIPPED,
            ])->count(),
            'rejected' => $orders->where('status', Order::STATUS_REJECTED)->count(),
            'items' => (int) $valid->sum(fn (Order $order) => $order->items->sum('quantity')),
            'revenue' => $paid['total'],
        ];

        $comparison = [
            'label' => $previous->translatedFormat('F Y'),
            'change' => $change,
        ];

        $pdf = Pdf::loadView('admin.report', [
            'period' => $month->translatedFormat('F Y'),
            'printedAt' => now($tz)->translatedFormat('d F Y, H:i'),
            'orders' => $orders,
            'products' => $products,
            'summary' => $summary,
            'comparison' => $comparison,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-donat-wawa-' . $month->format('Y-m') . '.pdf';

        return $request->boolean('unduh')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    private function paidTotals($from, $to): array
    {
        $row = Transaction::query()
            ->join('orders', 'orders.id_pesanan', '=', 'transactions.id_pesanan')
            ->where('transactions.status', Transaction::STATUS_SETTLED)
            ->whereBetween('transactions.waktu_bayar', [$from, $to])
            ->selectRaw('COUNT(*) as paid_count, COALESCE(SUM(orders.total), 0) as total, COALESCE(SUM(orders.subtotal), 0) as subtotal, COALESCE(SUM(orders.shipping_fee), 0) as shipping')
            ->first();

        return [
            'count' => (int) ($row->paid_count ?? 0),
            'total' => (int) ($row->total ?? 0),
            'subtotal' => (int) ($row->subtotal ?? 0),
            'shipping' => (int) ($row->shipping ?? 0),
        ];
    }
}