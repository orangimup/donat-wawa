<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RefundController extends Controller
{
    private const PER_PAGE = 10;

    private const SESSION_KEY = 'admin_refund_overrides';

    public const STATUS_LABELS = [
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'pending' => 'Pending',
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

        $all = $this->refunds();
        $refunds = $all;

        if ($status !== 'all' && isset(self::STATUS_LABELS[$status])) {
            $refunds = $refunds->where('status', $status);
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $refunds = $refunds->filter(fn (array $refund) => str_contains(
                mb_strtolower($refund['order_code'] . ' ' . $refund['code'] . ' ' . $refund['customer']),
                $needle
            ));
        }

        $refunds = match ($sort) {
            'oldest' => $refunds->sortBy('timestamp'),
            'customer' => $refunds->sortBy('customer', SORT_NATURAL | SORT_FLAG_CASE),
            'amount_high' => $refunds->sortByDesc('amount'),
            'amount_low' => $refunds->sortBy('amount'),
            default => $refunds->sortByDesc('timestamp'),
        };
        $refunds = $refunds->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $refunds->forPage($page, self::PER_PAGE)->values(),
            $refunds->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.refund-index', [
            'refunds' => $paginator,
            'pendingCount' => $all->where('status', 'pending')->count(),
            'totalRefunded' => $all->where('status', 'refunded')->sum('amount'),
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function show(int $refund): View
    {
        $refund = $this->findOrFail($refund);

        return view('admin.refund-show', [
            'refund' => $refund,
            'isFinal' => in_array($refund['status'], ['rejected', 'refunded'], true),
            'awaitingTransfer' => $refund['status'] === 'approved',
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    /**
     * Pending  -> Approved (no proof) | Refunded (approve + proof) | Rejected (reject + notes)
     * Approved -> Refunded (proof of transfer required)
     * Rejected / Refunded are final.
     */
    public function finalize(Request $request, int $refund): RedirectResponse
    {
        $current = $this->findOrFail($refund);

        if (! in_array($current['status'], ['pending', 'approved'], true)) {
            return back()->withErrors(['refund' => 'This refund request has already been finalized.']);
        }

        $awaitingTransfer = $current['status'] === 'approved';

        $validated = $request->validate([
            'decision' => ['required', $awaitingTransfer ? Rule::in(['approve']) : Rule::in(['approve', 'reject'])],
            'proof' => [
                Rule::requiredIf($awaitingTransfer),
                'nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:5120',
            ],
            'notes' => ['nullable', 'string', 'max:1000', 'required_if:decision,reject'],
        ], [
            'decision.required' => 'Please choose Approve or Reject.',
            'proof.required' => 'Please upload the proof of transfer.',
            'proof.mimes' => 'The proof must be a PNG, JPG or PDF file.',
            'proof.max' => 'The proof may not be larger than 5MB.',
            'notes.required_if' => 'Please write a note explaining why the refund is rejected.',
        ]);

        $notes = $validated['notes'] ?? null;

        if ($validated['decision'] === 'reject') {
            $this->saveOverride($refund, ['status' => 'rejected', 'notes' => $notes, 'proof_name' => null]);

            return redirect()->route('admin.refund')
                ->with('status', "Refund {$current['code']} has been rejected.");
        }

        if ($request->hasFile('proof')) {
            // The file itself is not stored yet (no refunds table). Only its name is kept.
            $this->saveOverride($refund, [
                'status' => 'refunded',
                'notes' => $notes ?? $current['notes'],
                'proof_name' => $request->file('proof')->getClientOriginalName(),
            ]);

            return redirect()->route('admin.refund')
                ->with('status', "Refund {$current['code']} has been marked as refunded.");
        }

        $this->saveOverride($refund, ['status' => 'approved', 'notes' => $notes]);

        return redirect()->route('admin.refund')
            ->with('status', "Refund {$current['code']} approved. Upload the proof of transfer to complete it.");
    }

    /* ------------------------------------------------------------------
     | Temporary data layer.
     | Refund requests are hardcoded until the refunds table exists.
     | Decisions made on the detail page are kept in the session so the
     | flow can be demonstrated. Replace refunds(), findOrFail() and
     | saveOverride() with Eloquent queries / updates later.
     ------------------------------------------------------------------ */

    private function refunds(): Collection
    {
        $overrides = session(self::SESSION_KEY, []);

        return $this->seed()->map(function (array $refund) use ($overrides) {
            if (isset($overrides[$refund['id']])) {
                $refund = array_merge($refund, $overrides[$refund['id']]);
            }

            return $refund;
        });
    }

    private function findOrFail(int $id): array
    {
        $refund = $this->refunds()->firstWhere('id', $id);

        abort_if($refund === null, 404);

        return $refund;
    }

    private function saveOverride(int $id, array $data): void
    {
        $overrides = session(self::SESSION_KEY, []);
        $overrides[$id] = array_merge($overrides[$id] ?? [], $data);

        session([self::SESSION_KEY => $overrides]);
    }

    private function seed(): Collection
    {
        $reasons = [
            'D-02' => [
                'label' => 'Damaged Goods (D-02)',
                'description' => 'The box arrived crushed on one side, and three of the seasonal glazed donuts were completely flattened into the cardboard. Unacceptable for a birthday gift.',
                'evidence' => 1,
            ],
            'W-01' => [
                'label' => 'Wrong Item (W-01)',
                'description' => 'I ordered Zen Matcha but received Salted Caramel instead. The order summary clearly says matcha, so this was a mix-up on your side.',
                'evidence' => 1,
            ],
            'N-01' => [
                'label' => 'Not Received (N-01)',
                'description' => 'The courier marked my order as delivered but nothing arrived. I already checked with my neighbors and the front desk.',
                'evidence' => 0,
            ],
            'Q-03' => [
                'label' => 'Quality Issue (Q-03)',
                'description' => 'The donuts were stale and dry when they arrived, which is not what I expect from a same-day order.',
                'evidence' => 2,
            ],
            'L-04' => [
                'label' => 'Late Delivery (L-04)',
                'description' => 'My pre-order arrived more than three hours after the promised time, so it missed the event completely.',
                'evidence' => 0,
            ],
        ];

        $banks = [
            'bca' => 'Bank Central Asia (BCA)',
            'bni' => 'Bank Negara Indonesia (BNI)',
            'mandiri' => 'Bank Mandiri',
            'bri' => 'Bank Rakyat Indonesia (BRI)',
        ];

        $evidenceImages = [
            asset('assets/images/donat.png'),
            asset('assets/images/donat-bundling.png'),
        ];

        // id, order no., customer, amount, status, reason, order date, request date, bank, last 4 digits, notes, proof file
        $rows = [
            [1, 1, 'Antonio', 28000, 'approved', 'D-02', '2026-08-30 14:32', '2026-09-01 15:40', 'bca', '4492', 'Approved. The transfer will be made within 1x24 hours.', null],
            [2, 2, 'Rina Wulandari', 20000, 'pending', 'D-02', '2026-08-31 14:32', '2026-09-02 10:15', 'bni', '7731', null, null],
            [3, 3, 'Budi Santoso', 24000, 'rejected', 'W-01', '2026-09-02 10:05', '2026-09-03 09:12', 'bni', '1183', 'The photos show the correct item was delivered, so we cannot process this refund.', null],
            [4, 4, 'Dewi Lestari', 36000, 'refunded', 'Q-03', '2026-09-03 11:20', '2026-09-04 08:47', 'mandiri', '7720', 'Refund transferred. Thank you for your patience.', 'transfer-proof-0004.png'],
            [5, 5, 'Keysha Marsha', 78000, 'refunded', 'N-01', '2026-09-04 16:45', '2026-09-05 13:30', 'bca', '3051', 'Full refund transferred.', 'transfer-proof-0005.pdf'],
            [6, 7, 'Siti Nurhaliza', 32000, 'rejected', 'L-04', '2026-09-06 10:03', '2026-09-07 12:15', 'bri', '6614', 'The order was delivered within the promised pre-order window.', null],
            [7, 8, 'Rizky Pratama', 18000, 'refunded', 'D-02', '2026-09-07 09:40', '2026-09-08 10:22', 'bni', '9027', 'Refund transferred.', 'transfer-proof-0007.png'],
            [8, 9, 'Nadia Putri', 15000, 'approved', 'Q-03', '2026-09-08 14:10', '2026-09-09 16:48', 'mandiri', '4408', 'Approved. Waiting for the transfer.', null],
            [9, 10, 'Hendra Wijaya', 38000, 'pending', 'D-02', '2026-09-09 12:33', '2026-09-10 09:05', 'bca', '2265', null, null],
            [10, 11, 'Antonio', 14000, 'pending', 'W-01', '2026-09-10 15:21', '2026-09-11 11:36', 'bri', '5379', null, null],
            [11, 12, 'Maya Anggraini', 68000, 'pending', 'N-01', '2026-09-12 14:32', '2026-09-13 10:18', 'bni', '8842', null, null],
            [12, 13, 'Galih Permana', 12000, 'pending', 'L-04', '2026-09-13 13:05', '2026-09-14 09:57', 'bca', '1930', null, null],
            [13, 14, 'Lestari Ningrum', 18000, 'pending', 'Q-03', '2026-09-14 11:48', '2026-09-15 15:24', 'mandiri', '7051', null, null],
        ];

        return collect($rows)->map(function (array $row) use ($reasons, $banks, $evidenceImages) {
            [$id, $orderNo, $customer, $amount, $status, $reasonKey, $orderAt, $requestAt, $bankKey, $last4, $notes, $proof] = $row;

            $orderDate = Carbon::parse($orderAt, 'Asia/Jakarta');
            $requestDate = Carbon::parse($requestAt, 'Asia/Jakarta');

            return [
                'id' => $id,
                'code' => '#REF-2026-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                'order_code' => '#DW-' . str_pad((string) $orderNo, 4, '0', STR_PAD_LEFT),
                'customer' => $customer,
                'amount' => $amount,
                'status' => $status,
                'timestamp' => $requestDate->timestamp,
                'request_label' => $requestDate->format('d M Y, H:i') . ' WIB',
                'order_label' => $orderDate->format('d F Y, H:i') . ' WIB',
                'reason' => $reasons[$reasonKey]['label'],
                'description' => $reasons[$reasonKey]['description'],
                'evidence' => array_slice($evidenceImages, 0, $reasons[$reasonKey]['evidence']),
                'bank_name' => $banks[$bankKey],
                'bank_holder' => $customer,
                'bank_last4' => $last4,
                'notes' => $notes,
                'proof_name' => $proof,
            ];
        });
    }
}