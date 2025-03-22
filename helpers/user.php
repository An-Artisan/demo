<?php
if(!function_exists('get_current_uid')) {
    function get_current_uid() {
        return $_SESSION['user']['user_id'];
    }
}