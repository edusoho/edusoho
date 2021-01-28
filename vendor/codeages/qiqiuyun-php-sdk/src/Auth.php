<?php

namespace QiQiuYun\SDK;

use QiQiuYun\SDK;

class Auth
{
    protected $accessKey;

    protected $secretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * 使用 HMAC 算法，对文本进行签名
     *
     * @param string $text 待签名的文本
     *
     * @return string 签名
     */
    public function makeSignature($text)
    {
        $signature = hash_hmac('sha1', $text, $this->secretKey, true);

        return  str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));
    }

    /**
     * 制作API请求的授权信息
     *
     * @param string $uri      HTTP 请求的 URI
     * @param string $body     HTTP 请求的 BODY
     * @param int    $lifetime 授权生命周期
     * @param bool   $useNonce 授权随机值避免重放攻击
     *
     * @return string 授权信息
     */
    public function makeRequestAuthorization($uri, $body = '', $lifetime = 600, $useNonce = true)
    {
        $nonce = $useNonce ? SDK\random_str('16') : 'no';
        $deadline = time() + $lifetime;
        $signature = $this->makeSignature("{$nonce}\n{$deadline}\n{$uri}\n{$body}");

        return "Signature {$this->accessKey}:{$deadline}:{$nonce}:{$signature}";
    }

    /**
     * 制作XAPI的请求授权信息
     */
    public function makeXAPIRequestAuthorization()
    {
        $deadline = strtotime(date('Y-m-d H:0:0', strtotime('+2 hours')));
        $signingText = $this->getAccessKey()."\n".$deadline;
        $signature = $this->getAccessKey().':'.$deadline.':'.$this->makeSignature($signingText);

        return "Signature $signature";
    }

    /**
     * 生成资源播放令牌
     *
     * @param string $resNo    资源编号
     * @param int    $lifetime 令牌的的有效时长，默认600秒
     * @param bool   $useNonce 是否使用随机值，防止重放攻击
     *
     * @return string 资源播放Token
     */
    public function makePlayToken($resNo, $lifetime = 600, $useNonce = true)
    {
        if ($useNonce) {
            $nonce = SDK\random_str('16');
        } else {
            $nonce = 'no';
        }

        $deadline = time() + $lifetime;
        $signingText = "{$resNo}\n{$nonce}\n{$deadline}";
        $signature = $this->makeSignature($signingText);

        return "{$nonce}:{$deadline}:{$signature}";
    }

    /**
     * 生成资源播放令牌 V2
     *
     * @param string $resNo    资源编号
     * @param array  $options  附加的选项参数
     * @param int    $lifetime 令牌的的有效时长，默认600秒
     * @param bool   $useNonce 是否使用随机值，防止重放攻击
     *
     * @return string 资源播放Token
     */
    public function makePlayToken2($resNo, $options = array(), $lifetime = 600, $useNonce = true)
    {
        if ($useNonce) {
            $nonce = SDK\random_str('16');
        } else {
            $nonce = 'no';
        }

        ksort($options);
        $options = http_build_query($options);

        $deadline = time() + $lifetime;
        $signingText = "{$resNo}\n{$options}\n{$deadline}\n{$nonce}";
        $signature = $this->makeSignature($signingText);

        return "{$deadline}:{$nonce}:{$signature}";
    }
}
