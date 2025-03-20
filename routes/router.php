<?php

/**
 * 路由文件
 * 该文件包含 Fat-Free Framework 的所有路由定义
 */
use \lib\Http\Middleware\AuthMiddleware;
use \lib\Http\Middleware\ThrottleMiddleware;
use \lib\Http\Middleware\Middleware;
// 引入框架实例
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once(__DIR__ . '/../vendor/autoload.php');
    $f3 = \Base::instance();
} else {
    $f3 = require(__DIR__ . '/../lib/base.php');
}

// 设置调试模式
$f3->set('DEBUG', 1);
if ((float)PCRE_VERSION < 8.0) {
    trigger_error('PCRE version is out of date');
}


// 加载配置文件
$f3->config('config.ini');
//$f3->set('AUTOLOAD', 'lib/Http/Middleware/');
require_once __DIR__ . '/../lib/Http/Middleware/AuthMiddleware.php';


// 绑定控制器方法 + 中间件（支持 Laravel 风格）
$f3->route('GET /home/@id', function($f3, $params) {
    Middleware::run([
        AuthMiddleware::class,  // 认证中间件
        ThrottleMiddleware::class  // 限流中间件
    ], 'lib\Http\Controllers\User\UserController->Test',[$f3, $params]);
});


/**
 * 定义路由
 */

// 首页路由
$f3->route('GET /', function ($f3) {
    $classes = array(
        'Base' => array(
            'hash',
            'json',
            'session',
            'mbstring'
        ),
        'Cache' => array(
            'apc',
            'apcu',
            'memcache',
            'memcached',
            'redis',
            'wincache',
            'xcache'
        ),
        'DB\SQL' => array(
            'pdo',
            'pdo_dblib',
            'pdo_mssql',
            'pdo_mysql',
            'pdo_odbc',
            'pdo_pgsql',
            'pdo_sqlite',
            'pdo_sqlsrv'
        ),
        'DB\Jig' => array('json'),
        'DB\Mongo' => array(
            'json',
            'mongo'
        ),
        'Auth' => array('ldap', 'pdo'),
        'Bcrypt' => array(
            'openssl'
        ),
        'Image' => array('gd'),
        'Lexicon' => array('iconv'),
        'SMTP' => array('openssl'),
        'Web' => array('curl', 'openssl', 'simplexml'),
        'Web\Geo' => array('geoip', 'json'),
        'Web\OpenID' => array('json', 'simplexml'),
        'Web\OAuth2' => array('json'),
        'Web\Pingback' => array('dom', 'xmlrpc'),
        'CLI\WS' => array('pcntl')
    );
    $f3->set('classes', $classes);
    $f3->set('content', 'welcome.htm');
    echo View::instance()->render('layout.htm');
});

// 用户参考页面路由
$f3->route('GET /userref', function ($f3) {
    $f3->set('content', 'userref.htm');
    echo View::instance()->render('layout.htm');
});




// 主页路由，带参数 ID
//$f3->route('GET /home/@id', 'lib\Http\Controllers\User\UserController->Test');  // 绑定控制器方法

return $f3;
