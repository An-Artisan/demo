<?php

// **初始化框架**
initFramework();

/**
 * 初始化 Fat-Free Framework
 */
function initFramework() {
    // 加载 Composer 自动加载（如果可用）
    loadAutoload();

    // 加载全局 Helper 方法
    loadHelpers();

    // 加载环境变量
    loadEnv();

    // 初始化 Fat-Free Framework
    $f3 = require(__DIR__ . '/routes/router.php');

    // 设置调试模式
    setupDebugMode($f3);

    // 加载配置
    loadConfig($f3);

    // 连接数据库
    setupDatabase($f3);

    // 运行框架
    $f3->run();
}

/**
 * 加载 Composer 自动加载（如果可用）
 */
function loadAutoload() {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    } elseif (!file_exists('app/base.php')) {
        die('Fat-Free Framework core not found. Run `git submodule init && git submodule update` or install via Composer.');
    } else {
        require 'app/base.php';
    }
}

/**
 * 加载全局 Helper 方法
 */
function loadHelpers() {
    require_once __DIR__ . '/helpers/helpers.php';
}

/**
 * 加载 `.env` 文件并解析环境变量
 */
function loadEnv() {
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                putenv("$key=$value");
            }
        }
    }
}

/**
 * 设置调试模式
 * @param $f3
 */
function setupDebugMode($f3) {
    $f3->set('DEBUG', 1);
    if ((float)PCRE_VERSION < 8.0) {
        trigger_error('PCRE version is out of date');
    }
}

/**
 * 加载配置文件
 * @param $f3
 */
function loadConfig($f3) {
    require_once __DIR__ . '/lib/config/EnvLoader.php';
    require_once __DIR__ . '/lib/config/Load.php';

    // 载入 Fat-Free 自带的 config.ini
//    $f3->config('config.ini');
}

/**
 * 初始化数据库连接
 * @param $f3
 */
function setupDatabase($f3) {
    $dbConfig = require_once __DIR__ . '/config/config.php';
    if (!isset($dbConfig['database']['host'], $dbConfig['database']['port'], $dbConfig['database']['dbname'])) {
        die('Database configuration is missing or incorrect.');
    }

    $f3->set('DB', new DB\SQL(
        "mysql:host={$dbConfig['database']['host']};port={$dbConfig['database']['port']};dbname={$dbConfig['database']['dbname']}",
        $dbConfig['database']['username'],
        $dbConfig['database']['password']
    ));
}
