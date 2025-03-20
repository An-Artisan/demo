<?php

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
        // 执行所有中间件
        foreach ($middlewares as $middleware) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                $instance->handle();
            }
        }

        // 如果 $next 是字符串（格式：Namespace\Class->Method），则解析并执行
        if (is_string($next) && strpos($next, '->') !== false) {
            list($class, $method) = explode('->', $next);
            $controller = new $class();
            return call_user_func_array([$controller, $method], $params);
        }

        // 如果 $next 是闭包，直接执行
        if ($next instanceof \Closure) {
            return call_user_func_array($next, $params);
        }
        return $next;
    }
}
