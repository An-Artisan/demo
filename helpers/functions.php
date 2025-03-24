<?php
if (!function_exists('format_number')) {
    function format_number($number) {
        $formatted = rtrim(rtrim((string)$number, '0'), '.');
        return $formatted === '' ? '0' : $formatted;
    }
}
