<?php
/**
 * Mail Configuration
 * Loads from environment variables
 */

return [
    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => env('MAIL_PORT', 587),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@novatis.com'),
        'name' => env('MAIL_FROM_NAME', 'Novatis')
    ],
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'smtp_auth' => true,
    'smtp_secure' => env('MAIL_ENCRYPTION', 'tls'),
];
