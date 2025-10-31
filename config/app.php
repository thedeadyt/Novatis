<?php
/**
 * Application Configuration
 */

return [
    'name' => env('APP_NAME', 'Novatis'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'Europe/Paris',

    'locale' => 'fr',
    'fallback_locale' => 'fr',
    'available_locales' => ['fr', 'en'],

    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120),
        'secure' => env('SESSION_SECURE', false),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => 'Lax'
    ],

    'security' => [
        'csrf_token_name' => env('CSRF_TOKEN_NAME', '_token'),
        'encryption_key' => env('ENCRYPTION_KEY'),
    ]
];
