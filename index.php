<?php
// 1. 加载 Composer 自动加载
require_once __DIR__ . '/vendor/autoload.php';

// 2. 加载全局辅助函数（包含 env/loadEnv/config/logger 等函数）
require_once __DIR__ . '/helpers/helpers.php';

// 3. 加载 .env 文件，确保 env() 生效
loadEnv(__DIR__ . '/.env');

// 4. 初始化 F3 实例，并全局共享
global $f3;
$f3 = \Base::instance();
$GLOBALS['f3'] = $f3;

// 5. 加载配置文件（依赖 env 函数）
$config = require __DIR__ . '/config/config.php';

// 6. 启动服务容器（db、logger 等）
require_once __DIR__ . '/lib/bootstrap/services.php';

// 7. 加载路由文件（包含所有控制器注册）
require_once __DIR__ . '/routes/router.php';

// 8. 设置调试模式
$f3->set('DEBUG', $config['app']['debug'] ?? 1);
if ((float)PCRE_VERSION < 8.0) {
    trigger_error('PCRE version is out of date');
}

//  启动应用
$f3->run();
