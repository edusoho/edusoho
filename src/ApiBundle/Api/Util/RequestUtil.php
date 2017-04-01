<?php

namespace ApiBundle\Api\Util;

use Symfony\Component\HttpFoundation\Request;

class RequestUtil
{
    /**
     * @var Request
     */
    private static $request;

    public static function setRequest(Request $request)
    {
        self::$request = $request;
    }

    public static function asset($path)
    {
        if (empty($path)) {
            return '';
        }
        if (strpos($path, self::$request->getScheme()."://") !== false) {
            return $path;
        }

        //public开头的特殊处理
        if (strpos($path, 'public://') !== false) {
            $path = '/files/'.str_replace('public://', '', $path);
        }

        return self::$request->getSchemeAndHttpHost().'/'.$path;
    }
}