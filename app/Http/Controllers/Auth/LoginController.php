<?php

namespace app\Http\Controllers\Auth;

use app\Http\Traits\JsonResponseTrait;

class LoginController
{
    use JsonResponseTrait;

    public function login($f3)
    {
        $username = $f3->get('POST.username');
        $password = $f3->get('POST.password');

        // 查询数据库
        $user = db()->exec("SELECT * FROM users WHERE username = ?", [$username]);

        if ($user) {
            $user = $user[0]; // 取第一条记录

            // 验证密码
            if (password_verify($password, $user['password_hash'])) {
                // 检查用户状态
                if ($user['status'] == 1) {
                    $this->error(403, '账号已被冻结');
                } else {
                    // 设置 session
                    $f3->set('SESSION.user', [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'balance' => json_decode($user['balance'], true)
                    ]);
                    $this->success([], '登录成功');
                }
            } else {
                $this->error(400, '用户名或密码错误');
            }
        } else {
            $this->error(400, '用户名或密码错误');
        }
    }
}
