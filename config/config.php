<?php
return [
    'app' => [
        'name' => env('APP_NAME', 'Fat-Free App'),
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
    ],
    'database' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'dbname' => env('DB_NAME', 'test'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', 'new_password'),
    ],
    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
    ],
    'logging' => [
        'level' => env('LOG_LEVEL', 'debug'),
        'log_mode' => env('LOG_MODE', 'console'),
    ],
    'gate' => [
        'api_key' => env('GATE_API_KEY', ''),
        'api_secret' => env('GATE_API_SECRET', ''),
    ]
];

