<?php

//  第一步：加载 helper（提供 env/loadEnv/config/logger 函数）
require_once __DIR__ . '/../../helpers/helpers.php';

use DB\SQL;
use lib\logging\BothLogger;
use lib\logging\ConsoleLogger;
use lib\logging\FileLogger;
use lib\logging\LoggerFactory;
date_default_timezone_set('Asia/Shanghai');

//  加载 .env（此时 helpers.php 已定义 loadEnv）
loadEnv(__DIR__ . '/../../.env');

//  初始化 F3
global $f3;
if (!isset($f3)) {
    $f3 = \Base::instance();
    $GLOBALS['f3'] = $f3;
}

//  加载配置（config.php 会用到 env()）
$configFile = __DIR__ . '/../../config/config.php';
if (!file_exists($configFile)) {
    throw new Exception('缺少配置文件 config/config.php');
}
$config = require $configFile;

// 数据库连接
if (!isset($db)) {
    try {
        $db = new DB\SQL(
            "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']}",
            $config['database']['username'],
            $config['database']['password']
        );
    } catch (\Exception $e) {
        dd($e->getMessage());
    }

    $f3->set('DB', $db);
}

// 日志注册
$consoleLogger = new ConsoleLogger();
$fileLogger = new FileLogger();
$bothLogger = new BothLogger($consoleLogger, $fileLogger);
$loggerFactory = new LoggerFactory($consoleLogger, $fileLogger, $bothLogger);
try {
    $logger = $loggerFactory->create($config['logging']['log_mode']);
} catch (Exception $e) {
    die($e->getMessage());
}
$f3->set('logger', $logger);
$f3->set('UI','ui/');
