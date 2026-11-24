<?php

namespace App\Services;

use App\Models\DeliveryZone;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    private const BASE = 'https://api.geoapify.com/v1';
    public function suggest(string $query): array
    {
        $key = 'shipping:suggest:' . sha1(mb_strtolower(trim($query)));

        return Cache::remember($key, 600, fn() => $this->fetchSuggestions($query));
    }

    private function fetchSuggestions(string $query): array
    {
        $bounds = config('shipping.search_bounds');
        $origin = config('shipping.origin');

        $response = $this->get('/geocode/autocomplete', [
            'text' => $query,
            'lang' => 'id',
            'limit' => 5,
            'format' => 'json',
            'filter' => sprintf('rect:%F,%F,%F,%F', $bounds['low']['lng'], $bounds['low']['lat'], $bounds['high']['lng'], $bounds['high']['lat']),
            'bias' => sprintf('proximity:%F,%F', $origin['lng'], $origin['lat']),
        ], 'autocomplete');

        $suggestions = [];

        foreach ($response->json('results', []) as $item) {
            $id = $item['place_id'] ?? null;

            if (!$id || !isset($item['lat'], $item['lon']) || isset($suggestions[$id])) {
                continue;
            }

            Cache::put('shipping:place:' . sha1($id), [
                'address' => (string) ($item['formatted'] ?? ''),
                'lat' => (float) $item['lat'],
                'lng' => (float) $item['lon'],
            ], (int) config('shipping.place_ttl'));

            $suggestions[$id] = [
                'place_id' => $id,
                'main' => (string) ($item['address_line1'] ?? $item['formatted'] ?? ''),
                'secondary' => (string) ($item['address_line2'] ?? ''),
            ];
        }

        return array_values($suggestions);
    }
    public function quote(string $placeId): array
    {
        $place = Cache::get('shipping:place:' . sha1($placeId));

        if ($place === null) {
            throw new ShippingException(
                __('We could not find that address. Please pick one from the suggestions.'),
                ShippingException::NOT_FOUND
            );
        }

        return ['address' => $place['address']] + $this->quoteByCoordinates($place['lat'], $place['lng']);
    }

    /**
     * Hitung ongkir dari titik koordinat (hasil pilih saran atau geser pin di peta).
     * Jarak & ongkir selalu dihitung ulang di server, tidak pernah dipercaya dari browser.
     */
    public function quoteByCoordinates(float $lat, float $lng): array
    {
        $bounds = config('shipping.search_bounds');

        if ($lat < $bounds['low']['lat'] || $lat > $bounds['high']['lat']
            || $lng < $bounds['low']['lng'] || $lng > $bounds['high']['lng']) {
            throw new ShippingException(
                __('Sorry, this address is outside our delivery area.'),
                ShippingException::OUT_OF_RANGE
            );
        }

        $lat = round($lat, 5);
        $lng = round($lng, 5);

        $distance = Cache::remember(
            'shipping:distance:' . sha1($lat . ',' . $lng),
            (int) config('shipping.quote_ttl'),
            fn() => round($this->roadDistanceKm($lat, $lng), 1)
        );

        $zone = DeliveryZone::forDistance($distance);

        if ($zone === null) {
            throw new ShippingException(
                __('Sorry, this address is outside our delivery area (:km km from the shop).', ['km' => number_format($distance, 1)]),
                ShippingException::OUT_OF_RANGE
            );
        }

        return [
            'distance_km' => $distance,
            'fee' => $zone->fee,
            'zone' => $zone->label,
        ];
    }

    private function roadDistanceKm(float $lat, float $lng): float
    {
        $origin = config('shipping.origin');

        if (!config('shipping.use_routing')) {
            return $this->haversineKm($origin['lat'], $origin['lng'], $lat, $lng) * (float) config('shipping.fallback_road_factor');
        }

        $waypoints = $origin['lat'] . ',' . $origin['lng'] . '|' . $lat . ',' . $lng;
        $modes = array_values(array_unique([config('shipping.travel_mode'), 'drive']));

        foreach ($modes as $mode) {
            try {
                $response = $this->get('/routing', ['waypoints' => $waypoints, 'mode' => $mode], 'routing (' . $mode . ')', false);
            } catch (ShippingException) {
                break;
            }

            $meters = $response->json('features.0.properties.distance') ?? $response->json('results.0.distance');

            if ($response->successful() && $meters !== null) {
                return $meters / 1000;
            }
        }

        return $this->haversineKm($origin['lat'], $origin['lng'], $lat, $lng) * (float) config('shipping.fallback_road_factor');
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthKm = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthKm * asin(min(1, sqrt($a)));
    }
    private function get(string $path, array $query, string $label, bool $failOnError = true): Response
    {
        $key = config('shipping.geoapify_key');

        if (empty($key)) {
            Log::error('Shipping: GEOAPIFY_API_KEY is not set.');
            throw $this->unavailable();
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout((int) config('shipping.connect_timeout', 3))
                ->timeout((int) config('shipping.http_timeout'))
                ->get(self::BASE . $path, $query + ['apiKey' => $key]);
        } catch (ConnectionException $e) {
            $this->logFailure($label, 0, $e->getMessage());
            throw $this->unavailable();
        }

        if ($failOnError && $response->failed()) {
            $this->logFailure($label, $response->status(), $response->body());
            throw $this->unavailable();
        }

        if (!$response->successful()) {
            $this->logFailure($label, $response->status(), $response->body());
        }

        return $response;
    }

    private function unavailable(): ShippingException
    {
        return new ShippingException(
            __('Address search is unavailable right now. Please try again in a moment.'),
            ShippingException::UNAVAILABLE,
            503
        );
    }

    private function logFailure(string $call, int $status, string $detail): void
    {
        $detail = str_replace((string) config('shipping.geoapify_key'), '[key]', $detail);

        Log::warning('Shipping: Geoapify ' . $call . ' failed', ['status' => $status, 'detail' => mb_substr($detail, 0, 500)]);
    }
}