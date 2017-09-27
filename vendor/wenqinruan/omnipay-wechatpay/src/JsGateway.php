<?php

namespace Omnipay\WechatPay;

/**
 * Class JsGateway
 * @package Omnipay\WechatPay
 */
class JsGateway extends BaseAbstractGateway
{

    public function getName()
    {
        return 'WechatPay JS API/MP';
    }


    public function getTradeType()
    {
        return 'JSAPI';
    }
}
