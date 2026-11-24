<?php

return [

    // Key untuk panggilan dari server (routing/jarak).
    'geoapify_key' => env('GEOAPIFY_API_KEY'),

    // Key untuk autocomplete & reverse geocode langsung dari browser.
    // Sebaiknya key terpisah yang dibatasi per domain (HTTP referrer) di dashboard Geoapify.
    'browser_key' => env('GEOAPIFY_BROWSER_KEY', env('GEOAPIFY_API_KEY')),

    'origin' => [
        'lat' => (float) env('SHOP_LAT', -7.9400),
        'lng' => (float) env('SHOP_LNG', 112.6540),
    ],

    'search_bounds' => [
        'low' => ['lat' => -8.06, 'lng' => 112.55],
        'high' => ['lat' => -7.82, 'lng' => 112.75],
    ],

    'travel_mode' => env('SHIPPING_TRAVEL_MODE', 'scooter'),

    // false = hitung jarak di server pakai rumus haversine x faktor jalan (tanpa panggilan jaringan,
    // aman untuk `php artisan serve`). true = pakai API routing Geoapify (lebih akurat, tapi bisa lambat).
    'use_routing' => (bool) env('SHIPPING_USE_ROUTING', false),

    'fallback_road_factor' => 1.3,

    'http_timeout' => 4,

    'connect_timeout' => 3,

    'place_ttl' => 7200,

    'quote_ttl' => 600,
];