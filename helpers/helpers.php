<?php

if (!function_exists('config')) {
    function config($key, $default = null) {
        global $f3;
        $value = $f3->get("config.$key");
        return $value !== null ? $value : $default;
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}
