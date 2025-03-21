<?php

// **初始化框架**
use lib\logging\BothLogger;
use lib\logging\ConsoleLogger;
use lib\logging\FileLogger;
use lib\logging\LoggerFactory;

try {
    initFramework();
} catch (Exception $e) {
    die($e->getMessage());
}

/**
 * 初始化 Fat-Free Framework
 * @throws Exception
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
    $config = require_once __DIR__ . '/config/config.php';

    // 设置调试模式
    setupDebugMode($f3);

    // 连接数据库
    setupDatabase($f3,$config);

    // 初始化logging
    setupLogging($f3,$config);
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
 * 初始化数据库连接
 * @param $f3
 */
function setupDatabase($f3,$config) {
    if (!isset($config['database']['host'], $config['database']['port'], $config['database']['dbname'])) {
        die('Database configuration is missing or incorrect.');
    }

    $f3->set('DB', new DB\SQL(
        "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']}",
        $config['database']['username'],
        $config['database']['password']
    ));
}

/**
 * 设置日志
 * @throws Exception
 */
function setupLogging($f3,$config) {
    // 创建日志实例
    $consoleLogger = new ConsoleLogger();
    $fileLogger = new FileLogger();
    $bothLogger = new BothLogger($consoleLogger,$fileLogger);
    $loggerFactory = new LoggerFactory($consoleLogger, $fileLogger,$bothLogger);
    $log = $loggerFactory->create($config['logging']['log_mode']);
    // 将日志实例注册到 F3 中，作为全局访问的变量
    $f3->set('log', $log);
    $f3->get('log')->write('This is an info message', 'info');
}
