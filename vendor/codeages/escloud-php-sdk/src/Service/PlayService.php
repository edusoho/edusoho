<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK;

class PlayService extends BaseService
{
    protected $host = 'play.qiqiuyun.net';
    protected $service = 'play';

    /**
     * 生成资源播放 Token
     *
     * @param string $no       资源编号
     * @param int    $lifetime Token 的的有效时长，默认600秒
     * @param array  $payload  载荷参数
     *
     * @return string 资源播放Token
     */
    public function makePlayToken($no, $lifetime = 600, $payload = array())
    {
        $payload = array_merge($payload, array(
            'no' => $no,
            'jti' => SDK\random_str('16'),
            'exp' => time() + $lifetime,
        ));

        return $this->auth->makeJwtToken($payload);
    }

    public function makePlayUrl($no, $lifetime = 600, $payload = array(), $options = array())
    {
        $token = $this->makePlayToken($no, $lifetime, $payload);

        $params = array_merge(array('resNo' => $no, 'token' => $token), $options);

        return sprintf('//%s/sdk_api/play?%s', $this->host, http_build_query($params));
    }
}
