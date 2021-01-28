<?php

namespace Omnipay\WechatPay;

/**
 * Class NativeGateway
 * @package Omnipay\WechatPay
 */
class NativeGateway extends BaseAbstractGateway
{
    public function getName()
    {
        return 'WechatPay Native';
    }


    public function getTradeType()
    {
        return 'NATIVE';
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\ShortenUrlRequest
     */
    public function shortenUrl($parameters = array())
    {
        return $this->createRequest('\Omnipay\WechatPay\Message\ShortenUrlRequest', $parameters);
    }
}
