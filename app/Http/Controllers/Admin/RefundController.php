<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderHistoryController;
use App\Models\Order;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RefundController extends Controller
{
    private const PER_PAGE = 10;

    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'picked_up' => 'Picked up',
        'refunded' => 'Refunded',
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
        $status = (string) $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));
        $sort = (string) $request->get('sort', 'latest');

        $query = Refund::query()
            ->select('refunds.*')
            ->join('orders', 'orders.id_pesanan', '=', 'refunds.id_pesanan')
            ->with('order.transaction');

        if ($status !== 'all' && isset(self::STATUS_LABELS[$status])) {
            $query->where('refunds.status', $status);
        }

        if ($search !== '') {
            $needle = '%' . ltrim($search, '#') . '%';
            $digits = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($needle, $digits) {
                $q->where('orders.id_pesanan', 'like', $needle)
                    ->orWhere('orders.customer_name', 'like', $needle);

                if ($digits !== '' && strlen($digits) <= 9) {
                    $q->orWhere('refunds.id', (int) ltrim(substr($digits, -4), '0') ?: 0);
                }
            });
        }

        match ($sort) {
            'oldest' => $query->orderBy('refunds.tgl_pengajuan')->orderBy('refunds.id'),
            'customer' => $query->orderBy('orders.customer_name'),
            'amount_high' => $query->orderByDesc('refunds.jumlah_refund')->orderByDesc('refunds.id'),
            'amount_low' => $query->orderBy('refunds.jumlah_refund')->orderByDesc('refunds.id'),
            default => $query->orderByDesc('refunds.tgl_pengajuan')->orderByDesc('refunds.id'),
        };

        return view('admin.refund-index', [
            'refunds' => $query->paginate(self::PER_PAGE)->withQueryString()
                ->through(fn(Refund $refund) => $this->present($refund)),
            'pendingCount' => Refund::whereIn('status', [Refund::STATUS_PENDING, Refund::STATUS_APPROVED, Refund::STATUS_PICKED_UP])->count(),
            'waitingBankCount' => $this->waitingBankCount(),
            'totalRefunded' => (int) Refund::where('status', Refund::STATUS_REFUNDED)->sum('jumlah_refund'),
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function show(int $refund): View
    {
        $model = Refund::with(['order.transaction'])->findOrFail($refund);

        return view('admin.refund-show', [
            'refund' => $this->present($model),
            'isFinal' => $model->stage() === 'final',
            'stage' => $model->stage(),
            'isCustomerRequest' => $model->asal_refund === Refund::SOURCE_CUSTOMER,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function finalize(Request $request, RefundService $refunds, int $refund): RedirectResponse
    {
        $current = Refund::with('order.transaction')->findOrFail($refund);
        $code = $this->code($current);
        $stage = $current->stage();

        if ($stage === 'final') {
            return back()->withErrors(['refund' => __('This refund request has already been finalized.')]);
        }

        $manual = $refunds->isManual($current);
        $customer = $current->asal_refund === Refund::SOURCE_CUSTOMER;

        $allowed = match (true) {
            $stage === 'pickup' => ['pickup'],
            $customer && $stage === 'review' => ['approve', 'reject'],
            default => ['approve'],
        };

        $rules = [
            'decision' => ['required', Rule::in($allowed)],
            'notes' => ['nullable', 'string', 'max:1000', 'required_if:decision,reject'],
        ];

        if ($manual && $stage === 'payout') {
            $rules['bukti_transfer'] = ['required_if:decision,approve', 'nullable', 'image', 'max:5120'];
        }

        $validated = $request->validate($rules, [
            'decision.required' => __('Please choose an action.'),
            'notes.required_if' => __('Please write a note explaining why the refund is rejected.'),
            'bukti_transfer.required_if' => __('Please upload the proof of the bank transfer.'),
        ]);

        $notes = $validated['notes'] ?? null;

        if ($validated['decision'] === 'reject') {
            if (!$refunds->reject($current, $notes)) {
                return back()->withErrors(['refund' => __('This refund request has already been finalized.')]);
            }

            return redirect()->route('admin.refund')->with('status', __('Refund :code has been rejected.', ['code' => $code]));
        }

        if ($validated['decision'] === 'pickup') {
            if (!$refunds->markPickedUp($current, $notes)) {
                return back()->withErrors(['refund' => __('This refund request has already been finalized.')]);
            }

            return redirect()->route('admin.refund.show', $current->id)
                ->with('status', __('Refund :code: the item has been marked as picked up. You can now process the refund.', ['code' => $code]));
        }

        if ($stage === 'review') {
            $result = $refunds->approve($current, $notes);

            if ($result->status !== Refund::STATUS_APPROVED) {
                return back()->withErrors(['refund' => __('This refund request has already been finalized.')]);
            }

            return redirect()->route('admin.refund.show', $current->id)
                ->with('status', __('Refund :code has been approved. Arrange for the courier to pick up the item.', ['code' => $code]));
        }

        if ($manual) {
            if (!$current->hasBankDetails()) {
                return back()->withErrors(['refund' => __('The customer has not provided bank details yet, so there is nowhere to transfer the refund.')]);
            }

            $proof = $request->file('bukti_transfer')->store('refund-transfer-proof', 'public');

            if (!$refunds->completeManually($current, $proof, $notes)) {
                Storage::disk('public')->delete($proof);

                return back()->withErrors(['refund' => __('This refund request has already been finalized.')]);
            }

            return redirect()->route('admin.refund')
                ->with('status', __('Refund :code has been marked as refunded (bank transfer).', ['code' => $code]));
        }

        $result = $refunds->approve($current, $notes);

        if ($result->status !== Refund::STATUS_REFUNDED) {
            return back()->withErrors([
                'refund' => __('Midtrans could not process this refund yet: :error You can try again.', ['error' => $result->pesan_error ?? __('unknown error')]),
            ]);
        }

        return redirect()->route('admin.refund')
            ->with('status', __('Refund :code has been sent back to the customer through Midtrans.', ['code' => $code]));
    }

    private function waitingBankCount(): int
    {
        $refunds = app(RefundService::class);

        return Refund::with('order.transaction')
            ->where('status', Refund::STATUS_PENDING)
            ->get()
            ->filter(fn(Refund $refund) => $refunds->isManual($refund) && !$refund->hasBankDetails())
            ->count();
    }

    private function code(Refund $refund): string
    {
        $year = ($refund->tgl_pengajuan ?? now())->format('Y');

        return '#REF-' . $year . '-' . str_pad((string) $refund->id, 4, '0', STR_PAD_LEFT);
    }

    private function paymentLogo(?string $label): ?string
    {
        if (blank($label)) {
            return null;
        }

        $known = ['bca', 'mandiri', 'qris', 'gopay', 'shopeepay', 'dana', 'ovo'];

        foreach (array_reverse(preg_split('/[^a-z0-9]+/', strtolower($label), -1, PREG_SPLIT_NO_EMPTY)) as $word) {
            if (in_array($word, $known, true) && file_exists(public_path("assets/payment/{$word}.svg"))) {
                return asset("assets/payment/{$word}.svg");
            }
        }

        return null;
    }

    private function present(Refund $refund): array
    {
        $manual = app(RefundService::class)->isManual($refund);
        $order = $refund->order;
        $transaction = $order?->transaction;
        $requestedAt = $refund->tgl_pengajuan?->copy()->timezone(Order::TIMEZONE);
        $reasons = OrderHistoryController::REFUND_REASONS + Refund::SYSTEM_REASONS;

        return [
            'id' => $refund->id,
            'code' => $this->code($refund),
            'order_code' => $order?->display_code ?? '-',
            'customer' => $order?->customer_name ?? '-',
            'amount' => $refund->jumlah_refund,
            'status' => $refund->status,
            'source' => Refund::SOURCE_LABELS[$refund->asal_refund] ?? $refund->asal_refund,
            'is_manual' => $manual,
            'payout' => $manual ? 'Manual bank transfer' : 'Midtrans',
            'bank' => $refund->hasBankDetails() ? [
                'name' => $refund->nama_bank,
                'number' => $refund->nomor_rekening,
                'holder' => $refund->nama_pemilik_rekening,
            ] : null,
            'proof' => $refund->bukti_transfer ? asset('storage/' . $refund->bukti_transfer) : null,
            'timestamp' => $requestedAt?->timestamp ?? 0,
            'request_label' => $requestedAt ? $requestedAt->translatedFormat('d M Y, H:i') . ' WIB' : '-',
            'request_date' => $requestedAt ? $requestedAt->translatedFormat('d M Y') : '-',
            'request_time' => $requestedAt ? $requestedAt->format('H:i') . ' WIB' : '',
            'source_key' => $refund->asal_refund,
            'needs_bank' => $manual && $refund->isOpen() && !$refund->hasBankDetails(),
            'stage' => $refund->stage(),
            'approved_label' => $refund->tgl_disetujui ? $refund->tgl_disetujui->copy()->timezone(Order::TIMEZONE)->translatedFormat('d M Y, H:i') . ' WIB' : null,
            'picked_up_label' => $refund->tgl_diambil ? $refund->tgl_diambil->copy()->timezone(Order::TIMEZONE)->translatedFormat('d M Y, H:i') . ' WIB' : null,
            'order_label' => $order?->placed_at ? $order->placed_at->translatedFormat('d F Y, H:i') . ' WIB' : '-',
            'reason' => $reasons[$refund->alasan] ?? $refund->alasan,
            'description' => $refund->detail_alasan,
            'evidence' => collect($refund->evidencePaths())->map(fn($path) => asset('storage/' . $path))->all(),
            'payment_method' => $transaction?->metode_pembayaran ?? '-',
            'payment_logo' => $this->paymentLogo($transaction?->metode_pembayaran),
            'midtrans_order_id' => $refund->id_pesanan,
            'midtrans_refund_id' => $refund->id_refund_midtrans,
            'notes' => $refund->catatan_admin,
            'error' => $refund->pesan_error,
        ];
    }
}