<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\AopCompletePurchaseRequest;
use Omnipay\Alipay\Requests\AopTradeCancelRequest;
use Omnipay\Alipay\Requests\AopTradeQueryRequest;
use Omnipay\Alipay\Requests\AopTradeRefundQueryRequest;
use Omnipay\Alipay\Requests\AopTradeRefundRequest;
use Omnipay\Alipay\Requests\AopTradeCloseRequest;
use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractAopGateway extends \Omnipay\Common\AbstractGateway
{
    protected $endpoints = array('production' => 'https://openapi.alipay.com/gateway.do', 'sandbox' => 'https://openapi.alipaydev.com/gateway.do');

    public function getDefaultParameters()
    {
        return array('format' => 'JSON', 'charset' => 'UTF-8', 'signType' => 'RSA', 'version' => '1.0', 'timestamp' => date('Y-m-d H:i:s'), 'alipaySdk' => 'lokielse/omnipay-alipay');
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->getParameter('app_id');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppId($value)
    {
        return $this->setParameter('app_id', $value);
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->getParameter('format');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setFormat($value)
    {
        return $this->setParameter('format', $value);
    }

    /**
     * @return mixed
     */
    public function getCharset()
    {
        return $this->getParameter('charset');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setCharset($value)
    {
        return $this->setParameter('charset', $value);
    }

    /**
     * @return mixed
     */
    public function getSignType()
    {
        return $this->getParameter('sign_type');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setSignType($value)
    {
        return $this->setParameter('sign_type', $value);
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->getParameter('version');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setVersion($value)
    {
        return $this->setParameter('version', $value);
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->getParameter('private_key');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setPrivateKey($value)
    {
        return $this->setParameter('private_key', $value);
    }

    /**
     * @return mixed
     */
    public function getEncryptKey()
    {
        return $this->getParameter('encrypt_key');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setEncryptKey($value)
    {
        return $this->setParameter('encrypt_key', $value);
    }

    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setNotifyUrl($value)
    {
        return $this->setParameter('notify_url', $value);
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->getParameter('timestamp');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setTimestamp($value)
    {
        return $this->setParameter('timestamp', $value);
    }

    /**
     * @return mixed
     */
    public function getAppAuthToken()
    {
        return $this->getParameter('app_auth_token');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppAuthToken($value)
    {
        return $this->setParameter('app_auth_token', $value);
    }

    /**
     * @return mixed
     */
    public function getAlipayPublicKey()
    {
        return $this->getParameter('alipay_public_key');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlipayPublicKey($value)
    {
        return $this->setParameter('alipay_public_key', $value);
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->getParameter('endpoint');
    }

    /**
     * @return mixed
     */
    public function getAlipaySdk()
    {
        return $this->getParameter('alipay_sdk');
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlipaySdk($value)
    {
        return $this->setParameter('alipay_sdk', $value);
    }

    public function production()
    {
        return $this->setEnvironment('production');
    }

    /**
     * @param $value
     *
     * @return $this
     *
     * @throws InvalidRequestException
     */
    public function setEnvironment($value)
    {
        $env = strtolower($value);
        if (!isset($this->endpoints[$env])) {
            throw new \Omnipay\Common\Exception\InvalidRequestException('The environment is invalid');
        }
        $this->setEndpoint($this->endpoints[$env]);

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setEndpoint($value)
    {
        return $this->setParameter('endpoint', $value);
    }

    public function sandbox()
    {
        return $this->setEnvironment('sandbox');
    }

    /**
     * @param array $parameters
     *
     * @return AopCompletePurchaseRequest
     *
     * @throws InvalidRequestException
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopCompletePurchaseRequest', $parameters);
    }

    /**
     * Query Order Status
     *
     * @param array $parameters
     *
     * @return AopTradeQueryRequest
     */
    public function query(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopTradeQueryRequest', $parameters);
    }

    /**
     * Refund
     *
     * @param array $parameters
     *
     * @return AopTradeRefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopTradeRefundRequest', $parameters);
    }

    /**
     * Query Refund Status
     *
     * @param array $parameters
     *
     * @return AopTradeRefundQueryRequest
     */
    public function refundQuery(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopTradeRefundQueryRequest', $parameters);
    }

    /**
     * Cancel Order
     *
     * @param array $parameters
     *
     * @return AopTradeCancelRequest
     */
    public function cancel(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopTradeCancelRequest', $parameters);
    }

    /**
     * Close Order
     *
     * @param array $parameters
     *
     * @return AopTradeCloseRequest
     */
    public function close(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopTradeCloseRequest', $parameters);
    }

    /**
     * Settle
     *
     * @param array $parameters
     *
     * @return AopTradeCancelRequest
     */
    public function settle(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\AopTradeOrderSettleRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function queryBillDownloadUrl(array $parameters = array())
    {
        return $this->createRequest('Omnipay\\Alipay\\Requests\\DataServiceBillDownloadUrlQueryRequest', $parameters);
    }
}
