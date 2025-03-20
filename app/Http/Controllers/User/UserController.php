<?php

namespace app\Http\Controllers\User;


use app\Http\Traits\JsonResponseTrait;
use lib\config\Load;

class UserController
{
    use JsonResponseTrait;

    function Test($f3, $params)
    {
//        $f3->set('content', 'welcome.htm');
//        echo \View::instance()->render('layout.htm');
        var_dump(Load::get('app.name'));
        // 获取路由参数
        $id = $params['id'];
        $name = $f3->get('GET.name');  // 获取 name 参数
        $this->success(["id" => $id, "name" => $name, "app_name" => config('database.default')], "获取用户成功");
    }
}
