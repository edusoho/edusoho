<?php

namespace ApiBundle\Api\Util;

class RequestUtil
{
    public static function asset($path)
    {
        if (empty($path)) {
            return '';
        }
        if (strpos($path, static::getSchema()."://") !== false) {
            return $path;
        }

        //public开头的特殊处理
        if (strpos($path, 'public://') !== false) {
            $path = '/files/'.str_replace('public://', '', $path);
        }

        return static::getHttpHost().$path;
    }

    public static function getHttpHost()
    {
        return static ::getSchema()."://{$_SERVER['HTTP_HOST']}";
    }

    public static function getSchema()
    {
        if(!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS'])) {
            $schema = 'https';
        } else {
            $schema = 'http';
        }

        return $schema;
    }
}