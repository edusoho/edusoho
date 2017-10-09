<?php

namespace Omnipay\WechatPay;

/**
 * Class Gateway
 * @package Omnipay\WechatPay
 */
class Gateway extends \Omnipay\WechatPay\BaseAbstractGateway
{
    public function getName()
    {
        return 'WechatPay';
    }
}