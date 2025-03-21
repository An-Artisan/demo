<?php


// 封装数据库函数
if (!function_exists('db')) {
    function db() {
        return \Base::instance()->get('DB');
    }
}
