<?php

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return \Codeages\Biz\Framework\Utility\Env::get($key, $default);
    }
}
