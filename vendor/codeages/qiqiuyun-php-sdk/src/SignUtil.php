<?php

namespace QiQiuYun\SDK;

use QiQiuYun\SDK;

class SignUtil
{
    public static function serialize($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('In json hmac specification serialize data must be array.');
        }

        ksort($data);

        $json = json_encode($data);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_encode error: '.json_last_error_msg());
        }

        return $json;
    }

    public static function cut($str, $length = 512)
    {
        return substr($str, 0, $length);
    }

    public static function sign($auth, $str)
    {
        $time = time();
        $once = SDK\random_str('16');
        $signText = implode('\n', array($time, $once, $str));
        $sign = $auth->sign($signText);
        $accessKey = $auth->getAccessKey();

        return "{$accessKey}:{$time}:{$once}:{$sign}";
    }
}
