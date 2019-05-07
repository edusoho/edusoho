<?php

namespace Omnipay\WechatPay\Message;

/**
 * Class ShortenUrlResponse
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_3&index=5
 */
class ShortenUrlResponse extends \Omnipay\WechatPay\Message\BaseAbstractResponse
{
    public function getShortUrl()
    {
        $data = $this->getData();
        return isset($data['short_url']) ? $data['short_url'] : null;
    }
}