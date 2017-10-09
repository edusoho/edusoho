<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Message\AbstractRequest;
/**
 * Class BaseAbstractRequest
 * @package Omnipay\WechatPay\Message
 */
abstract class BaseAbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->getParameter('app_id');
    }
    /**
     * @param mixed $appId
     */
    public function setAppId($appId)
    {
        $this->setParameter('app_id', $appId);
    }
    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->getParameter('api_key');
    }
    /**
     * @param mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->setParameter('api_key', $apiKey);
    }
    /**
     * @return mixed
     */
    public function getMchId()
    {
        return $this->getParameter('mch_id');
    }
    /**
     * @param mixed $mchId
     */
    public function setMchId($mchId)
    {
        $this->setParameter('mch_id', $mchId);
    }
}