<?php

namespace Omnipay\WechatPay;

/**
 * Class AppGateway
 * @package Omnipay\WechatPay
 */
class AppGateway extends BaseAbstractGateway
{

    public function getName()
    {
        return 'WechatPay App';
    }


    public function getTradeType()
    {
        return 'APP';
    }
}
