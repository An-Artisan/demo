<?php

if (!function_exists('config')) {
    function config($key, $default = null) {
        global $f3;
        $value = $f3->get("config.$key");
        return $value !== null ? $value : $default;
    }
}
