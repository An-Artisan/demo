<?php

namespace lib\Http\Middleware;

use lib\Http\Traits\JsonResponseTrait;

class AuthMiddleware extends Middleware {
    use JsonResponseTrait;
    public function handle() {
        if (!isset($_GET['auth'])) {
            $this->error(401,"未授权，请先登录",[]);
        }
    }
}
