<?php

namespace Codeages\Biz\Framework\Utility;

class Env
{
    public static function get($key, $default = null)
    {
        $value = getenv($key);

        if (false === $value) {
            return $default;
        }

        if ('false' === $value) {
            return false;
        } elseif ('true' === $value) {
            return true;
        }

        return $value;
    }

    public static function load(array $env = array())
    {
        foreach ($env as $key => $value) {
            if (true === $value) {
                $value = 'true';
            } elseif (false === $value) {
                $value = 'false';
            }
            putenv("$key=$value");
        }
    }
}
