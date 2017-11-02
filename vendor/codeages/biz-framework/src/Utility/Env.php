<?php

namespace Codeages\Biz\Framework\Utility;

class Env
{
    public static function get($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        if ($value === 'false') {
            return false;
        } elseif ($value === 'true') {
            return true;
        }

        return $value;
    }

    public static function load(array $env = array())
    {
        foreach ($env as $key => $value) {
            if ($value === true) {
                $value = 'true';
            } elseif ($value === false) {
                $value = 'false';
            }
            putenv("$key=$value");
        }
    }
}
