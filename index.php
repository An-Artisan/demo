<?php

if (file_exists('vendor/autoload.php')) {
    require_once('vendor/autoload.php');
} elseif (!file_exists('app/base.php')) {
    die('fatfree-core not found. Run `git submodule init` and `git submodule update` or install via composer with `composer install`.');
} else {
    require('app/base.php');
}

// 引入路由
$f3 = require(__DIR__ . '/routes/router.php');

// 设置调试模式
$f3->set('DEBUG', 1);
if ((float)PCRE_VERSION < 8.0) {
    trigger_error('PCRE version is out of date');
}

require_once __DIR__ . '/lib/config/EnvLoader.php';
require_once __DIR__ . '/lib/config/Load.php';

// **全局 helper 方法**
require_once __DIR__ . '/helpers/helpers.php';

// 加载.env
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($key, $value) = explode('=', $line, 2);
        putenv("$key=$value");
    }
}
// 加载配置文件
$f3->config('config.ini');


// **设置自动加载路径**
// 运行框架
$f3->run();
