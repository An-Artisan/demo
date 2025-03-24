<?php
if (!function_exists('format_number')) {
    function format_number($number) {
        $str = (string)$number;
        // 如果没有小数点，则认为是整数，直接返回
        if (strpos($str, '.') === false) {
            return $str;
        }
        $formatted = rtrim(rtrim($str, '0'), '.');
        return $formatted === '' ? '0' : $formatted;
    }
}
