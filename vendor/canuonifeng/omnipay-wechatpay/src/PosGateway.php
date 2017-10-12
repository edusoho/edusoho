<?php

namespace Omnipay\WechatPay;

/**
 * Class PosGateway
 * @package Omnipay\WechatPay
 */
class PosGateway extends \Omnipay\WechatPay\BaseAbstractGateway
{
    public function getName()
    {
        return 'WechatPay Pos';
    }
    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\CreateOrderRequest
     */
    public function purchase($parameters = array())
    {
        $parameters['trade_type'] = $this->getTradeType();
        return $this->createRequest('\\Omnipay\\WechatPay\\Message\\CreateMicroOrderRequest', $parameters);
    }
    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\QueryOpenIdByAuthCodeRequest
     */
    public function queryOpenId($parameters = array())
    {
        return $this->createRequest('\\Omnipay\\WechatPay\\Message\\QueryOpenIdByAuthCodeRequest', $parameters);
    }
}