<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK;

class PlayService extends BaseService
{
    protected $host = 'play.qiqiuyun.net';

    /**
     * 生成资源播放 Token
     *
     * @param string $no       资源编号
     * @param int    $lifetime Token 的的有效时长，默认600秒
     * @param array  $options  参数
     *
     * @return string 资源播放Token
     */
    public function makePlayToken($no, $lifetime = 600, $options = array())
    {
        $payload = array_merge($options, array(
            'no' => $no,
            'jti' => SDK\random_str('16'),
            'exp' => time() + $lifetime,
        ));

        return $this->auth->makeJwtToken($payload);
    }
}
