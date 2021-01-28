<?php

namespace Codeages\Biz\Pay\Util;

class RandomToolkit
{
    public static function generateInt($length)
    {
        $code = rand(0, 9);
        for ($i = 1; $i < $length; ++$i) {
            $code = $code.rand(0, 9);
        }
        return $code;
    }

    public static function generateString($length = 32, $strPool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz')
    {
        $str = '';
        $max = strlen($strPool)-1;

        for($i=0;$i<$length;$i++){
            $str .= $strPool[rand(0, $max)];
        }

        return $str;
    }
}
