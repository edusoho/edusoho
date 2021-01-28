<?php

namespace QiQiuYun\SDK\Service;

class PlayService extends BaseService
{
    protected $host = array('play1.qiqiuyun.net', 'play2.qiqiuyun.net');

    /**
     * 生成资源播放 Token
     *
     * @param string $resNo    资源编号
     * @param int    $lifetime Token 的有效时长
     * @param bool   $useNonce Token是否一次性
     *
     * @return string 资源播放Token
     */
    public function makePlayToken($resNo, $lifetime = 600, $useNonce = true)
    {
        return $this->auth->makePlayToken($resNo, $lifetime, $useNonce);
    }

    /**
     * 获取云资源的播放地址，该地址可以直接嵌入iframe播放
     *
     * @param string $resNo    资源编号
     * @param int    $lifetime 播放地址的有效时长
     * @param bool   $useNonce 播放地址是否一次性
     *
     * @return string 播放地址
     */
    public function getPlaySrc($resNo, $lifetime = 600, $useNonce = true)
    {
        $src = $this->getRequestUri('/player', 'auto');

        $params = array(
            'resNo' => $resNo,
            'token' => $this->makePlayToken($resNo, $lifetime, $useNonce),
        );

        return $src.'?'.http_build_query($params);
    }

    /**
     * 获取云资源的播放元信息
     *
     * @param $string $resNo
     * @param int  $lifetime
     * @param bool $useNonce
     *
     * @return array 资源播放元信息
     */
    public function getPlayMeta($resNo, $lifetime = 600, $useNonce = true)
    {
        $url = $this->getRequestUri('/js/v1/play');

        $params = array(
            'resNo' => $resNo,
            'token' => $this->makePlayToken($resNo, $lifetime, $useNonce),
        );

        $url = $url.'?'.http_build_query($params);

        $response = $this->createClient()->request('GET', $url);

        return $this->extractResultFromResponse($response);
    }
}
