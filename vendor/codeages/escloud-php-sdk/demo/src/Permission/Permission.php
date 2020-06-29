<?php

namespace ESCloud\SDKDemo\Permission;

use Exception;

class Permission
{
    /**
     * @param $exp
     * @param $token
     * @throws Exception
     */
    public static function check($exp, $token)
    {
        $key = '1234567890abcdef';
        $exp = $_GET['exp'];
        $token = $_GET['token'];

        $time = time();
        if ($exp - $time > 3600 || $exp < $time) {
            throw new Exception('有效期错误');
        }

        if ($token != md5($key . $exp)) {
            throw new Exception('无效token');
        }
    }
}
