<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\Exception\SDKException;
use QiQiuYun\SDK\TokenGenerator\TokenGenerator;
use QiQiuYun\SDK\TokenGenerator\PublicTokenGenerator;

class ResourceService
{
    protected $apiHost;

    protected $playHost;

    protected $accessKey;

    protected $secretKey;

    protected $tokenGenerator;

    public function __construct(array $options = array())
    {
        $options = array_merge(array(
            'access_key' => '',
            'secret_key' => '',
            'token_generator' => null,
            'api_host' => 'api.edusoho.net',
            'play_host' => 'play.qiqiuyun.net',
        ), $options);

        if (!$options['access_key']) {
            throw new SDKException('Required "access_key" key no supplied in options.');
        }

        if (!$options['secret_key']) {
            throw new SDKException('Required "secret_key" key no supplied in options.');
        }

        if ($options['token_generator']) {
            if (!$options['token_generator'] instanceof TokenGenerator) {
                throw new SDKException('"token_generator" must be instanceof TokenGenerator.');
            }
            $this->tokenGenerator = $options['token_generator'];
        } else {
            $this->tokenGenerator = new PublicTokenGenerator($options['access_key'], $options['secret_key']);
        }

        $this->accessKey = $options['access_key'];
        $this->secretKey = $options['secret_key'];
        $this->playHost = $options['play_host'];
        $this->apiHost = $options['api_host'];
    }

    /**
     * 获得资源信息
     *
     * @param $resNo string 资源编号
     */
    public function get($resNo)
    {
        return $this->client->get("/resources/{$resNo}");
    }

    /**
     * 生成资源播放 Token
     *
     * @param string $resNo    资源编号
     * @param int    $lifetime Token 的有效时长
     * @param bool   $once     Token是否一次性
     *
     * @return string 资源播放Token
     */
    public function generatePlayToken($resNo, $lifetime = 600, $once = true)
    {
        return $this->tokenGenerator->generatePlayToken($resNo, $lifetime, $once);
    }

    /**
     * 获取云资源的播放地址，该地址可以直接嵌入iframe播放
     *
     * @param string $resNo    资源编号
     * @param int    $lifetime 播放地址的有效时长
     * @param bool   $once     播放地址是否一次性
     *
     * @return string 播放地址
     */
    public function getPlaySrc($resNo, $lifetime = 600, $once = true)
    {
        $src = "//{$this->playHost}/player";
        $params = array(
            'resNo' => $resNo,
            'token' => $this->generatePlayToken($resNo, $lifetime, $once),
        );

        return $src.'?'.http_build_query($params);
    }
}
