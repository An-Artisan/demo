<?php

/**
 * 解析 .env 文件，并提供 env() 函数
 */

if (!function_exists('loadEnv')) {
    function loadEnv($filePath) {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // 忽略注释
            }
            list($key, $value) = explode('=', $line, 2);
            putenv(trim("$key=$value"));
        }
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

// 解析 .env 文件
loadEnv(__DIR__ . '/.env');
