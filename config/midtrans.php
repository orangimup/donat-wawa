<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'enabled_payments' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('MIDTRANS_ENABLED_PAYMENTS', 'gopay,dana,ovo,shopeepay,bca_va,echannel'))
    ))),

    'expiry_minutes' => (int) env('MIDTRANS_EXPIRY_MINUTES', 60),
];