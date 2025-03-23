<?php
if(!function_exists('format_number')) {
    function format_number($number) {
        return rtrim(rtrim((string)$number,'0'),'.');
    }
}