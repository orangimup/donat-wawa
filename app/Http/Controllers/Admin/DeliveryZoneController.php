<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    private const PER_PAGE = 5;

    private const SESSION_KEY = 'admin_delivery_zones';

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

        // After deleting the last row of a page, go to the new last page.
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

        $zones = $this->zones();
        $zone = $this->makeZone(
            ($zones->max('id') ?? 0) + 1,
            $data['min_km'],
            $data['max_km'],
            $data['fee'],
            $data['is_active']
        );

        $this->save($zones->push($zone));

        return back()->with('status', "Delivery zone {$zone['label']} has been added.");
    }

    public function update(Request $request, int $zone): RedirectResponse
    {
        $current = $this->findOrFail($zone);
        $data = $this->validated($request, $zone);

        $updated = $this->makeZone($zone, $data['min_km'], $data['max_km'], $data['fee'], $data['is_active']);

        $this->save($this->zones()->map(fn (array $item) => $item['id'] === $zone ? $updated : $item));

        return back()->with('status', "Delivery zone {$current['label']} has been updated.");
    }

    public function destroy(int $zone): RedirectResponse
    {
        $current = $this->findOrFail($zone);

        $this->save($this->zones()->reject(fn (array $item) => $item['id'] === $zone));

        return back()->with('status', "Delivery zone {$current['label']} has been deleted.");
    }

    /**
     * Validates the popup form. "distance_range" is typed like "0 - 2 km" and is
     * turned into min/max numbers; ranges may touch but must not overlap.
     */
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
                        $fail("This range overlaps with the {$overlap['label']} zone.");

                        return;
                    }

                    $parsed = $range;
                },
            ],
            'fee' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'in:0,1'],
        ], [
            'distance_range.required' => 'Please enter the distance range.',
            'fee.required' => 'Please enter the delivery fee.',
            'fee.integer' => 'The delivery fee must be a whole number.',
            'fee.min' => 'The delivery fee cannot be negative.',
            'fee.max' => 'The delivery fee is too large.',
        ]);

        return [
            'min_km' => $parsed[0],
            'max_km' => $parsed[1],
            'fee' => (int) $request->input('fee'),
            'is_active' => $request->input('is_active') === '1',
        ];
    }

    /** "0 - 2 km", "2.5-5", "5 – 8 KM" -> [min, max], or null when it does not look like a range. */
    private function parseRange(string $raw): ?array
    {
        $number = '(\d+(?:[.,]\d+)?)';

        if (! preg_match("/^\s*{$number}\s*[-–]\s*{$number}\s*(?:km)?\s*$/i", $raw, $m)) {
            return null;
        }

        return [(float) str_replace(',', '.', $m[1]), (float) str_replace(',', '.', $m[2])];
    }

    /* ------------------------------------------------------------------
     | Temporary data layer.
     | Zones are hardcoded until the delivery_zones table exists. Changes
     | are kept in the session (the whole list) so add / edit / delete can
     | be demonstrated. Replace zones(), findOrFail() and save() with
     | Eloquent queries later.
     ------------------------------------------------------------------ */

    private function zones(): Collection
    {
        return collect(session(self::SESSION_KEY, $this->seed()->all()));
    }

    private function findOrFail(int $id): array
    {
        $zone = $this->zones()->firstWhere('id', $id);

        abort_if($zone === null, 404);

        return $zone;
    }

    private function save(Collection $zones): void
    {
        session([self::SESSION_KEY => $zones->values()->all()]);
    }

    private function seed(): Collection
    {
        return collect([
            $this->makeZone(1, 0, 2, 3000, true),
            $this->makeZone(2, 2, 5, 5000, true),
            $this->makeZone(3, 5, 8, 8000, true),
            $this->makeZone(4, 8, 10, 10000, true),
            $this->makeZone(5, 10, 15, 15000, true),
            $this->makeZone(6, 15, 20, 20000, false),
            $this->makeZone(7, 20, 25, 25000, false),
        ]);
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