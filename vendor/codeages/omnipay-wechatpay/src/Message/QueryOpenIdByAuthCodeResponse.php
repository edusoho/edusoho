<?php

namespace Omnipay\WechatPay\Message;

/**
 * Class QueryOpenIdByAuthCodeResponse
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_13&index=9
 */
class QueryOpenIdByAuthCodeResponse extends \Omnipay\WechatPay\Message\BaseAbstractResponse
{
    public function getOpenId()
    {
        $data = $this->getData();
        return isset($data['openid']) ? $data['openid'] : null;
    }
}