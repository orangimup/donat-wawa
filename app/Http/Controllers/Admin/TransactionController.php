<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    private const PER_PAGE = 10;

    public const STATUS_LABELS = [
        'settled' => 'Settled',
        'pending' => 'Pending',
        'expired' => 'Expired',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
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

        // Sorting/searching by customer and amount needs the order columns, so join once.
        $query = Transaction::query()
            ->join('orders', 'orders.id_pesanan', '=', 'transactions.id_pesanan')
            ->select('transactions.*')
            ->with('order');

        if ($status !== 'all' && isset(self::STATUS_LABELS[$status])) {
            $query->where('transactions.status', $status);
        }

        if ($search !== '') {
            $term = ltrim($search, '#');
            $like = '%' . $term . '%';

            $query->where(function ($q) use ($like, $term) {
                $q->where('transactions.id_pesanan', 'like', $like)
                    ->orWhere('orders.customer_name', 'like', $like)
                    ->orWhere('transactions.metode_pembayaran', 'like', $like);

                // "#TRX-00012" / "TRX-12" -> transactions.id
                if (preg_match('/^TRX-0*(\d+)$/i', $term, $m)) {
                    $q->orWhere('transactions.id', (int) $m[1]);
                }
            });
        }

        match ($sort) {
            'oldest' => $query->orderBy('transactions.created_at')->orderBy('transactions.id'),
            'customer' => $query->orderBy('orders.customer_name'),
            'amount_high' => $query->orderByDesc('orders.total')->orderByDesc('transactions.id'),
            'amount_low' => $query->orderBy('orders.total')->orderByDesc('transactions.id'),
            default => $query->orderByDesc('transactions.created_at')->orderByDesc('transactions.id'),
        };

        $paginator = $query
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn(Transaction $trx) => $this->present($trx));

        return view('admin.transaction-index', [
            'transactions' => $paginator,
            'stats' => $this->stats(),
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    private function present(Transaction $trx): array
    {
        $at = ($trx->waktu_bayar ?? $trx->created_at)->clone()->timezone(Order::TIMEZONE);

        return [
            'id' => $trx->id,
            'code' => $trx->displayCode(),
            'customer' => $trx->order->customer_name,
            'amount' => $trx->order->total,
            'method' => $trx->metode_pembayaran ?? '-',
            'status' => $trx->status,
            'timestamp' => $at->timestamp,
            'date_label' => $at->translatedFormat('d M Y') . ',',
            'time_label' => $at->format('H:i:s') . ' WIB',
        ];
    }

    private function stats(): array
    {
        $tz = Order::TIMEZONE;
        $monthStart = now($tz)->startOfMonth()->utc();
        $lastMonthStart = now($tz)->subMonthNoOverflow()->startOfMonth()->utc();

        $paidSince = fn($from, $to = null) => Transaction::query()
            ->join('orders', 'orders.id_pesanan', '=', 'transactions.id_pesanan')
            ->where('transactions.status', Transaction::STATUS_SETTLED)
            ->where('transactions.waktu_bayar', '>=', $from)
            ->when($to, fn($q) => $q->where('transactions.waktu_bayar', '<', $to));

        $revenue = (int) $paidSince($monthStart)->sum('orders.total');
        $lastRevenue = (int) $paidSince($lastMonthStart, $monthStart)->sum('orders.total');

        $badge = $lastRevenue > 0
            ? sprintf('%+d%%', round((($revenue - $lastRevenue) / $lastRevenue) * 100))
            : null;

        $pending = Transaction::where('status', Transaction::STATUS_PENDING)->count();
        $successful = Transaction::whereIn('status', [Transaction::STATUS_SETTLED])
            ->where('waktu_bayar', '>=', $monthStart)->count();
        $failed = Transaction::whereIn('status', [Transaction::STATUS_FAILED, Transaction::STATUS_EXPIRED])
            ->where('updated_at', '>=', $monthStart)->count();

        return [
            ['title' => 'Total Revenue', 'value' => $this->compactRupiah($revenue), 'badge' => $badge, 'caption' => 'this month', 'featured' => true, 'icon' => 'trend'],
            ['title' => 'Pending Payments', 'value' => (string) $pending, 'badge' => null, 'caption' => 'right-now', 'featured' => false, 'icon' => 'cart'],
            ['title' => 'Successful Payments', 'value' => (string) $successful, 'badge' => null, 'caption' => 'this month', 'featured' => false, 'icon' => 'cart'],
            ['title' => 'Failed Payments', 'value' => (string) $failed, 'badge' => null, 'caption' => 'this month', 'featured' => false, 'icon' => 'cart'],
        ];
    }

    private function compactRupiah(int $amount): string
    {
        return match (true) {
            $amount >= 1_000_000_000 => rtrim(rtrim(number_format($amount / 1_000_000_000, 1), '0'), '.') . 'B',
            $amount >= 1_000_000 => rtrim(rtrim(number_format($amount / 1_000_000, 1), '0'), '.') . 'M',
            $amount >= 1_000 => rtrim(rtrim(number_format($amount / 1_000, 1), '0'), '.') . 'K',
            default => (string) $amount,
        };
    }
}