<?php

namespace QiQiuYun\SDK\Service;

/**
 * Play V2 Service
 */
class PlayV2Service extends BaseService
{
    protected $host = array('play1.qiqiuyun.net', 'play2.qiqiuyun.net');

    /**
     * 生成资源播放 Token
     *
     * @param string $resNo    资源编号
     * @param int    $lifetime Token 的有效时长
     * @param bool   $useNonce Token 是否一次性
     *
     * @return string 资源播放Token
     */
    public function makePlayToken($resNo, $options = array(), $lifetime = 600, $useNonce = true)
    {
        return $this->auth->makePlayToken2($resNo, $options, $lifetime, $useNonce);
    }

    /**
     * 生成获取播放元信息的地址
     *
     * @param string $resNo    资源编号
     * @param array  $options  选项
     * @param int    $lifetime Token 有效时长
     * @param bool   $useNonce Token 是否一次性
     */
    public function makePlayMetaUrl($resNo, $options = array(), $lifetime = 600, $useNonce = true)
    {
        $url = $this->getRequestUri('/js/v2/play', 'auto');

        $params = array(
            'resNo' => $resNo,
            'token' => $this->makePlayToken($resNo, $options, $lifetime, $useNonce),
            'o' => $options,
        );

        return $url.'?'.http_build_query($params);
    }
}
