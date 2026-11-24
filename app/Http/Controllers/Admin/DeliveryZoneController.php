<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    private const PER_PAGE = 5;

    public const STATUS_LABELS = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public function __construct()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));
        $sort = $request->get('sort', 'distance');

        $zones = $this->zones();

        if ($status !== 'all' && isset(self::STATUS_LABELS[$status])) {
            $zones = $zones->where('status', $status);
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $zones = $zones->filter(fn (array $zone) => str_contains(
                mb_strtolower($zone['label'] . ' ' . $zone['fee']),
                $needle
            ));
        }

        $zones = match ($sort) {
            'latest' => $zones->sortByDesc('id'),
            'fee_low' => $zones->sortBy('fee'),
            'fee_high' => $zones->sortByDesc('fee'),
            default => $zones->sortBy('min_km'),
        };
        $zones = $zones->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $lastPage = max(1, (int) ceil($zones->count() / self::PER_PAGE));
        if ($page > $lastPage) {
            $request->session()->reflash();

            return redirect()->route('admin.delivery-zone', array_merge($request->query(), ['page' => $lastPage]));
        }

        $paginator = new LengthAwarePaginator(
            $zones->forPage($page, self::PER_PAGE)->values(),
            $zones->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.delivery-zone-index', [
            'zones' => $paginator,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'statusLabels' => self::STATUS_LABELS,
            'retryMode' => $request->old('_mode'),
            'retryId' => $request->old('_zone_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $zone = DeliveryZone::create($data);

        return back()->with('status', __('Delivery zone :label has been added.', ['label' => $zone->label]));
    }

    public function update(Request $request, int $zone): RedirectResponse
    {
        $model = DeliveryZone::findOrFail($zone);
        $label = $model->label;

        $model->update($this->validated($request, $zone));

        return back()->with('status', __('Delivery zone :label has been updated.', ['label' => $label]));
    }

    public function destroy(int $zone): RedirectResponse
    {
        $model = DeliveryZone::findOrFail($zone);
        $label = $model->label;

        $model->delete();

        return back()->with('status', __('Delivery zone :label has been deleted.', ['label' => $label]));
    }
    private function validated(Request $request, ?int $ignoreId): array
    {
        $parsed = null;

        $request->validate([
            'distance_range' => [
                'required', 'string', 'max:30',
                function (string $attribute, mixed $value, \Closure $fail) use (&$parsed, $ignoreId) {
                    $range = $this->parseRange((string) $value);

                    if ($range === null) {
                        $fail('Enter the range like "0 - 2 km".');

                        return;
                    }

                    [$min, $max] = $range;

                    if ($min >= $max) {
                        $fail('The start of the range must be smaller than the end.');

                        return;
                    }

                    $overlap = $this->zones()->first(
                        fn (array $zone) => $zone['id'] !== $ignoreId && $min < $zone['max_km'] && $max > $zone['min_km']
                    );

                    if ($overlap) {
                        $fail(__('This range overlaps with the :label zone.', ['label' => $overlap['label']]));

                        return;
                    }

                    $parsed = $range;
                },
            ],
            'fee' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'in:0,1'],
        ], [
            'distance_range.required' => __('Please enter the distance range.'),
            'fee.required' => __('Please enter the delivery fee.'),
            'fee.integer' => __('The delivery fee must be a whole number.'),
            'fee.min' => __('The delivery fee cannot be negative.'),
            'fee.max' => __('The delivery fee is too large.'),
        ]);

        return [
            'min_km' => $parsed[0],
            'max_km' => $parsed[1],
            'fee' => (int) $request->input('fee'),
            'is_active' => $request->input('is_active') === '1',
        ];
    }
    private function parseRange(string $raw): ?array
    {
        $number = '(\d+(?:[.,]\d+)?)';

        if (! preg_match("/^\s*{$number}\s*[-–]\s*{$number}\s*(?:km)?\s*$/i", $raw, $m)) {
            return null;
        }

        return [(float) str_replace(',', '.', $m[1]), (float) str_replace(',', '.', $m[2])];
    }
    private function zones(): Collection
    {
        return DeliveryZone::query()
            ->orderBy('min_km')
            ->get()
            ->map(fn (DeliveryZone $z) => $this->makeZone($z->id, $z->min_km, $z->max_km, $z->fee, $z->is_active));
    }

    private function makeZone(int $id, float $min, float $max, int $fee, bool $active): array
    {
        $format = fn (float $km) => rtrim(rtrim(number_format($km, 1, '.', ''), '0'), '.');

        return [
            'id' => $id,
            'min_km' => $min,
            'max_km' => $max,
            'label' => $format($min) . ' - ' . $format($max) . ' km',
            'fee' => $fee,
            'status' => $active ? 'active' : 'inactive',
        ];
    }
}