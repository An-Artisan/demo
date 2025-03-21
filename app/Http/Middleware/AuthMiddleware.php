<?php

namespace app\Http\Middleware;

use app\Http\Traits\JsonResponseTrait;

class AuthMiddleware extends Middleware {
    public function handle($f3) {
        if (!isset($_GET['auth'])) {
            $this->error(401,"未授权，请先登录",[]);
        }
        logger()->write("hello", 'info');
    }
}
