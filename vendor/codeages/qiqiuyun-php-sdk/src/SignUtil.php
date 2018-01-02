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

    /**
     * 给所给的数组，先对里面的每个json对象进行key排序，将整个字符串转化为json, 再截取前100位
     *
     * @param $data 格式为
     *  [
     *      {
     *          'd' => 1,
     *          'a' => 2
     *      }, ....
     *  ]
     */
    public static function serializeJsonArrayAndCut($data)
    {
        $serializedData = array();
        foreach ($data as $singleData) {
            ksort($singleData);
            array_push($serializedData, $singleData);
        }

        $jsonStr = json_encode($serializedData);

        return substr($jsonStr, 0, 100);
    }

    public function serializeJsonAndCut($data)
    {
        $sortedJsonStr = self::serialize($data);
        $jsonStr = json_encode($data);

        return substr($jsonStr, 0, 100);
    }

    public static function sign($auth, $jsonStr)
    {
        $time = time();
        $once = SDK\random_str('16');
        $signText = implode('\n', array($time, $once, $jsonStr));
        $sign = $auth->sign($signText);
        $accessKey = $auth->getAccessKey();

        return "{$accessKey}:{$time}:{$once}:{$sign}";
    }
}
