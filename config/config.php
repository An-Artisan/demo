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
        'dbname' => env('DB_DATABASE', 'fatfree'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ],
    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
    ],
    'logging' => [
        'level' => env('LOG_LEVEL', 'debug'),
    ],
];
