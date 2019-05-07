<?php

namespace Omnipay\WechatPay;

/**
 * Class MwebGateway
 * @package Omnipay\WechatPay
 */
class MwebGateway extends BaseAbstractGateway
{

    public function getName()
    {
        return 'WechatPay Mweb';
    }


    public function getTradeType()
    {
        return 'MWEB';
    }
}
