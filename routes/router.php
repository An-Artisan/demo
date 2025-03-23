<?php

/**
 * 路由文件
 * 该文件包含 Fat-Free Framework 的所有路由定义
 */
use \app\Http\Middleware\AuthMiddleware;
use \app\Http\Middleware\ThrottleMiddleware;
use \app\Http\Middleware\Middleware;
// 引入框架实例
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once(__DIR__ . '/../vendor/autoload.php');
    $f3 = \Base::instance();
} else {
    $f3 = require(__DIR__ . '/../app/base.php');
}




// ✅ 用户相关路由
$f3->route('GET /users', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\User\UserController->index', [$f3, $params]);
}); // 获取所有用户

$f3->route('GET /users/@id', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\User\UserController->show', [$f3, $params]);
}); // 获取单个用户

$f3->route('POST /users', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\User\UserController->store', [$f3, $params]);
}); // 创建用户

$f3->route('PUT /users/@id', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\User\UserController->update', [$f3, $params]);
}); // 更新用户

$f3->route('DELETE /users/@id', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\User\UserController->destroy', [$f3, $params]);
}); // 删除用户

// ✅ 资产数据（API 和 本地都加上中间件）
$f3->route('GET /users/get-balance', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\User\UserController->getBalance', [$f3, $params]);
}); // API资产

$f3->route('GET /users/get-balance-local', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\User\UserController->getBalanceLocal', [$f3, $params]);
}); // 本地资产

// ✅ 登录相关（无需中间件）
$f3->route('POST /api/login', 'app\Http\Controllers\Auth\LoginController->login'); // 登录API

$f3->route('GET /login', function($f3) {
    $f3->set('content', 'login.htm');
    echo View::instance()->render('layout.htm');
}); // 登录页面

$f3->route('GET /logout', function($f3) {
    $f3->clear('SESSION.user');
    $f3->reroute('/login');
}); // 退出登录

// ✅ 后台首页（直接在逻辑里判断）
$f3->route('GET /dashboard', function($f3) {
    if (!$f3->exists('SESSION.user')) {
        $f3->reroute('/login');
    }
    $f3->set('user', $f3->get('SESSION.user'));
    $f3->set('content', 'dashboard.htm');
    echo View::instance()->render('layout.htm');
});

// ✅ 币种模块
$f3->route('GET /coins/get-currency-list', 'app\Http\Controllers\Coins\CoinsController->getCurrencyList');
$f3->route('GET /coins/get-currency-info', 'app\Http\Controllers\Coins\CoinsController->getCurrencyInfo');
$f3->route('GET /coins/get-currency-kline', 'app\Http\Controllers\Coins\CoinsController->getCurrencyKline');
$f3->route('GET /coins/get-currency-depth', 'app\Http\Controllers\Coins\CoinsController->getCurrencyDepth');
$f3->route('GET /coins/get-currency-depth-local', 'app\Http\Controllers\Order\OrderController->getCurrentOrderListLocal');
$f3->route('GET /coins/get-currency-trade', 'app\Http\Controllers\Coins\CoinsController->getCurrencyTrade');
$f3->route('GET /coins/get-index-data', 'app\Http\Controllers\Coins\CoinsController->getIndexData');

// ✅ 订单模块（建议保护订单操作）
$f3->route('POST /order/create-order', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\Order\OrderController->createOrder', [$f3, $params]);
}); // 下单

$f3->route('POST /order/cancel-order', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\Order\OrderController->cancelOrder', [$f3, $params]);
}); // 取消订单



$f3->route('GET /order/get-current-order-list', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\Order\OrderController->getCurrentOrderList', [$f3, $params]);
}); // 当前委托

$f3->route('GET /order/get-history-order-list', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\Order\OrderController->getHistoryOrderList', [$f3, $params]);
}); // 历史委托

$f3->route('GET /order/get-filled-order-list', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\Order\OrderController->getFilledOrderList', [$f3, $params]);
}); // 成交订单

$f3->route('GET /order/get-latest-trades', function($f3, $params) {
    Middleware::run([AuthMiddleware::class], 'app\Http\Controllers\Order\OrderController->getLatestTrades', [$f3, $params]);
}); // 最新成交

// ✅ 图表数据（如需保护也可加中间件）
$f3->route('GET /trade/chart', 'app\Http\Controllers\Order\TradeController->chart');

// ✅ 首页
$f3->route('GET /', function($f3) {
    echo 'Hello World!';
});

return $f3;
