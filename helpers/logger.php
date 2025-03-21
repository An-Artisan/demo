<?php

if (!function_exists('logger')) {
    function logger() {
        return \Base::instance()->get('logger');
    }
}
