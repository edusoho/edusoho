<?php

namespace Biz\Distributor\Util;

class DistributorUtil
{
    public static function getType($token)
    {
        $splitedStr = explode(':', $token);
        return $splitedStr[1] ? $splitedStr[1] : 'course';
    }
}
