<?php

declare(strict_types=1);

return [
    'default' => env('PAYMENT_DRIVER', 'manual'),
    'mode' => env('PAYMENT_MODE', 'test'),
    'currency' => env('PAYMENT_CURRENCY', env('APP_CURRENCY', 'USD')),

    'drivers' => [
        'manual' => [
            'label' => 'Bank Transfer',
            'instructions' => env('PAYMENT_BANK_TRANSFER_INSTRUCTIONS', ''),
        ],
        'stripe' => [
            'label' => 'Stripe',
            'public_key' => env('STRIPE_PUBLIC_KEY', ''),
            'secret_key' => env('STRIPE_SECRET_KEY', ''),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        ],
        'paypal' => [
            'label' => 'PayPal',
            'client_id' => env('PAYPAL_CLIENT_ID', ''),
            'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
            'webhook_secret' => env('PAYPAL_WEBHOOK_SECRET', ''),
            'checkout_url' => env('PAYPAL_CHECKOUT_URL', ''),
        ],
        'omise' => [
            'label' => 'Omise',
            'public_key' => env('OMISE_PUBLIC_KEY', ''),
            'secret_key' => env('OMISE_SECRET_KEY', ''),
            'webhook_secret' => env('OMISE_WEBHOOK_SECRET', ''),
            'checkout_url' => env('OMISE_CHECKOUT_URL', ''),
        ],
    ],
];
