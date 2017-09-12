<?php

namespace Omnipay\WechatPay;

use Omnipay\Common\AbstractGateway;

abstract class BaseAbstractGateway extends AbstractGateway
{

    public function setTradeType($tradeType)
    {
        $this->setParameter('trade_type', $tradeType);
    }


    public function setAppId($appId)
    {
        $this->setParameter('app_id', $appId);
    }


    public function getAppId()
    {
        return $this->getParameter('app_id');
    }


    public function setApiKey($apiKey)
    {
        $this->setParameter('api_key', $apiKey);
    }


    public function getApiKey()
    {
        return $this->getParameter('api_key');
    }


    public function setMchId($mchId)
    {
        $this->setParameter('mch_id', $mchId);
    }


    public function getMchId()
    {
        return $this->getParameter('mch_id');
    }


    public function setNotifyUrl($url)
    {
        $this->setParameter('notify_url', $url);
    }


    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }


    /**
     * @return mixed
     */
    public function getCertPath()
    {
        return $this->getParameter('cert_path');
    }


    /**
     * @param mixed $certPath
     */
    public function setCertPath($certPath)
    {
        $this->setParameter('cert_path', $certPath);
    }


    /**
     * @return mixed
     */
    public function getKeyPath()
    {
        return $this->getParameter('key_path');
    }


    /**
     * @param mixed $keyPath
     */
    public function setKeyPath($keyPath)
    {
        $this->setParameter('key_path', $keyPath);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\CreateOrderRequest
     */
    public function purchase($parameters = array ())
    {
        $parameters['trade_type'] = $this->getTradeType();

        return $this->createRequest('\Omnipay\WechatPay\Message\CreateOrderRequest', $parameters);
    }


    public function getTradeType()
    {
        return $this->getParameter('trade_type');
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\CompletePurchaseRequest
     */
    public function completePurchase($parameters = array ())
    {
        return $this->createRequest('\Omnipay\WechatPay\Message\CompletePurchaseRequest', $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\QueryOrderRequest
     */
    public function query($parameters = array ())
    {
        return $this->createRequest('\Omnipay\WechatPay\Message\QueryOrderRequest', $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\CloseOrderRequest
     */
    public function close($parameters = array ())
    {
        return $this->createRequest('\Omnipay\WechatPay\Message\CloseOrderRequest', $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\RefundOrderRequest
     */
    public function refund($parameters = array ())
    {
        return $this->createRequest('\Omnipay\WechatPay\Message\RefundOrderRequest', $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\QueryOrderRequest
     */
    public function queryRefund($parameters = array ())
    {
        return $this->createRequest('\Omnipay\WechatPay\Message\QueryRefundRequest', $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\WechatPay\Message\DownloadBillRequest
     */
    public function downloadBill($parameters = array ())
    {
        return $this->createRequest('\Omnipay\WechatPay\Message\DownloadBillRequest', $parameters);
    }
}
