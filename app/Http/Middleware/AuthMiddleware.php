<?php

namespace app\Http\Middleware;

use app\Http\Traits\JsonResponseTrait;

class AuthMiddleware extends Middleware {
    use JsonResponseTrait;

    public function handle($f3): bool
    {
        // 开启 session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 判断是否登录（是否有 user 信息）
        if (!isset($_SESSION['user']) || empty($_SESSION['user']['user_id'])) {
             $this->error(401, "未登录或 session 失效，请重新登录", []);
        }

        // 可选：记录登录用户信息
        logger()->write("用户已登录，用户ID：" . $_SESSION['user']['user_id'], 'info');

        // 放行中间件
        return true;
    }
}
