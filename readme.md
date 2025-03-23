# 📌 项目完整文档

买单类型	卖单类型	是否支持	撮合逻辑	成交价格来源
市价单	限价单	✅	优先成交	卖方限价价
限价单	市价单	✅	优先成交	买方限价价
限价单	限价单	✅	当买价 ≥ 卖价时	卖方限价价
市价单	市价单	❌（一般不支持）	应避免匹配	❌ 无价格可参考


本项目是基于 PHP 开发的 Web 框架，包含 Artisan 命令、定时任务、日志系统、配置管理等功能。

## 📂 目录结构

```plaintext
app/                # 应用层代码
├── Console/        # 命令行相关代码
│   ├── Kernel.php  # 任务调度核心
│   ├── Commands/   # 自定义命令
│       ├── TestJob.php  # 示例任务命令
├── Constants/      # 常量定义
├── Exceptions/     # 自定义异常
├── Http/           # HTTP 层
│   ├── Controllers/   # 控制器
│   ├── Middleware/    # 中间件
│   ├── Traits/        # 通用 Trait
├── Models/         # 数据库模型
├── Services/       # 业务逻辑层

config/             # 配置文件目录
helpers/            # 全局辅助函数
lib/                # 核心功能库
├── bootstrap/      # 框架启动逻辑
├── config/         # 配置管理
├── encryption/     # 加解密模块
├── logging/        # 日志系统
├── schedule/       # 定时任务调度

routes/             # 路由定义
storage/            # 存储目录
├── logs/           # 日志存储
├── cache/          # 缓存存储

ui/                 # 前端页面
```

---

## 📜 关键文件说明


# 中间件系统说明

## 1️⃣ 中间件概述
中间件用于在请求处理流程中执行额外的逻辑，如 **权限验证**、**日志记录**、**请求拦截** 等。  
本框架的中间件系统设计允许多个中间件按顺序执行，并最终调用目标控制器或闭包函数。

---

## 2️⃣ 中间件基类 (`Middleware.php`)
📌 **路径**: `app/Http/Middleware/Middleware.php`  
📌 **功能**: 提供统一的中间件运行机制，负责调用指定的中间件，并最终执行控制器方法或闭包。

### **代码解析**
```php
namespace app\Http\Middleware;

abstract class Middleware {
    /**
     * 运行中间件并调用目标函数
     *
     * @param array $middlewares 需要执行的中间件类数组
     * @param mixed $next 闭包或控制器方法字符串（如 'app\Http\Controllers\User\UserController->Test'）
     * @param array $params 额外的参数（如 Fat-Free Framework 的 `$f3` 和 `$params`）
     */
    public static function run(array $middlewares, $next, array $params = []) {
        $f3 = \Base::instance(); // 获取 F3 实例

        // 执行所有中间件
        foreach ($middlewares as $middleware) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                $instance->handle($f3);
            }
        }

        // 解析控制器字符串 "Namespace\Class->Method"
        if (is_string($next) && strpos($next, '->') !== false) {
            list($class, $method) = explode('->', $next);
            $controller = new $class();
            return call_user_func_array([$controller, $method], $params);
        }

        // 如果是闭包，直接执行
        if ($next instanceof \Closure) {
            return call_user_func_array($next, $params);
        }
        
        return $next;
    }
}


n
```
核心逻辑
✅ 动态执行多个中间件 - 通过 foreach 循环实例化并执行 handle 方法。
✅ 支持两种回调：

控制器方法（格式：Namespace\Class->Method）
闭包函数
✅ 自动解析 Fat-Free Framework ($f3) 实例，以便在中间件中使用。

## 3️⃣ 自定义中间件示例
在 app/Http/Middleware/ 目录下新建 AuthMiddleware.php:
```php
namespace app\Http\Middleware;

class AuthMiddleware extends Middleware {
    public function handle($f3) {
        if (!$f3->get('SESSION.user')) {
            echo "未授权访问，禁止请求！";
            exit;
        }
    }
}

```
#### 📌 作用:
当用户未登录时，拦截请求并返回 "未授权访问"。

## 4️⃣ 如何使用中间件
在 routes/router.php 里：

```php
use app\Http\Middleware\AuthMiddleware;
use app\Http\Middleware\Middleware;

Middleware::run(
    [AuthMiddleware::class],   // 需要执行的中间件列表
    'app\Http\Controllers\User\UserController->profile', // 目标控制器方法
    [$f3, $params]  // 额外参数
);

```

#### 📌 流程：

先执行 AuthMiddleware，检查用户是否已登录。
如果通过，调用 UserController->profile。
如果不通过，拦截请求并终止执行。

## 5️⃣ 其他常见中间件


### 📄 中间件文件列表

| 📄 文件 | 📌 作用 |
|---------|--------|
| `app/Http/Middleware/AuthMiddleware.php` | 认证中间件，检查用户是否已登录 |
| `app/Http/Middleware/ThrottleMiddleware.php` | 限流中间件，防止用户请求过于频繁 |
| `app/Http/Middleware/CorsMiddleware.php` | 处理跨域请求 |
| `app/Http/Middleware/LoggingMiddleware.php` | 请求日志记录 |



## 6️⃣ 总结
Middleware.php 是所有中间件的基类。
所有中间件都必须实现 handle($f3) 方法。
支持链式执行多个中间件，并最终调用目标控制器或闭包。


##  命令行实现
### `artisan` - Artisan 命令入口
用于执行命令行任务，例如：

```sh
php artisan TestJob run
php artisan schedule:ru
```
---

### `app/Console/Kernel.php` - 定时任务调度核心
```php
namespace app\Console;

class Kernel {
    public function commands(): array {
        return [
            'TestJob' => app\Console\Commands\TestJob::class,
        ];
    }

    public function schedule() {
        (new app\Console\Commands\TestJob(\Base::instance()))->run();
    }
}
```
- `commands()`：注册可用的命令
- `schedule()`：调度定时任务

---

### `app/Console/Commands/TestJob.php` - 自定义 Artisan 任务
```php
namespace app\Console\Commands;

class TestJob {
    protected $f3;

    public function __construct($f3) {
        $this->f3 = $f3;
    }

    public function run() {
        logger()->write("hello job", 'info');
        echo "运行 TestJob::run 成功
";
    }
}
```
- `run()`：执行具体的任务逻辑
- `logger()->write()`：记录日志

---

### `config/config.php` - 配置文件
```php
return [
    'app' => [
        'name' => 'Fat-Free App',
        'env' => 'production',
        'debug' => false,
    ],
    'logging' => [
        'log_mode' => 'console',
    ],
];
```
- `app`：应用配置
- `logging`：日志级别

---

### `lib/logging/ConsoleLogger.php` - 终端日志实现
```php
class ConsoleLogger implements LoggerInterface {
    protected $useColors;

    public function write(string $message, string $level) {
        if (!$this->isCli()) {
            return;
        }

        $logMessage = "[{$level}] {$message}" . PHP_EOL;

        if ($this->isInteractiveShell()) {
            $color = $this->getColorForLevel($level);
            $logMessage = "[{$color}m{$logMessage}[0m";
        }

        echo $logMessage;
    }
}
```
- 处理 CLI 模式下的日志
- 自动添加颜色

---

### `lib/schedule/ScheduledTask.php` - 定时任务调度器
```php
namespace lib\schedule;

class ScheduledTask {
    protected $job;
    protected $method;
    protected $expression;

    public function __construct(string $job, string $method = 'handle') {
        $this->job = $job;
        $this->method = $method;
    }

    public function everyMinute() {
        $this->expression = '* *';
        return $this;
    }

    public function shouldRunNow(): bool {
        return date('H:i') === $this->expression;
    }

    public function run() {
        $class = "app\Console\Commands\{$this->job}";
        $instance = new $class(\Base::instance());

        if (method_exists($instance, $this->method)) {
            $instance->{$this->method}();
        }
    }
}
```
- `everyMinute()`：每分钟运行
- `shouldRunNow()`：检查是否应该运行任务
- `run()`：执行任务

---

### `routes/router.php` - 路由定义
```php
$f3->route('GET /', 'app\Http\Controllers\HomeController->index');
```
- 绑定 URL `/` 到 `HomeController->index`

---

## 📌 运行方式

1. **启动项目**
   ```sh
   php -S localhost:8000 -t public
   ```

2. **运行 Artisan 命令**
   ```sh
   php artisan TestJob run
   ```

3. **执行定时任务**
   ```sh
   php artisan schedule:run
   ```

---

## 🔧 其他说明

- **如何配置 Crontab**
  ```sh
  * * * * * /usr/local/opt/php@7.1/bin/php /path/to/project/artisan schedule:run >> /path/to/project/storage/logs/cron.log 2>&1
  ```
    - 每分钟执行定时任务
    - 日志记录到 `cron.log`

---

**本 README 提供了完整的文件结构说明、关键代码解析，以及如何运行和调试本框架。**

