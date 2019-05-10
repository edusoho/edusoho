<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;
/**
 * Class QueryOrderRequest
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_2&index=4
 * @method QueryOrderResponse send()
 */
class QueryOrderRequest extends \Omnipay\WechatPay\Message\BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/pay/orderquery';
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('app_id', 'mch_id');
        if (!$this->getTransactionId() && !$this->getOutTradeNo()) {
            throw new \Omnipay\Common\Exception\InvalidRequestException('The \'transaction_id\' or \'out_trade_no\' parameter is required');
        }
        $data = array('appid' => $this->getAppId(), 'mch_id' => $this->getMchId(), 'transaction_id' => $this->getTransactionId(), 'out_trade_no' => $this->getOutTradeNo(), 'nonce_str' => md5(uniqid()));
        $data = array_filter($data);
        $data['sign'] = \Omnipay\WechatPay\Helper::sign($data, $this->getApiKey());
        return $data;
    }
    /**
     * @return mixed
     */
    public function getOutTradeNo()
    {
        return $this->getParameter('out_trade_no');
    }
    /**
     * @param mixed $outTradeNo
     */
    public function setOutTradeNo($outTradeNo)
    {
        $this->setParameter('out_trade_no', $outTradeNo);
    }
    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->getParameter('transaction_id');
    }
    public function setTransactionId($transactionId)
    {
        $this->setParameter('transaction_id', $transactionId);
    }
    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $request = $this->httpClient->post($this->endpoint)->setBody(\Omnipay\WechatPay\Helper::array2xml($data));
        $response = $request->send()->getBody();
        $responseData = \Omnipay\WechatPay\Helper::xml2array($response);
        return $this->response = new \Omnipay\WechatPay\Message\QueryOrderResponse($this, $responseData);
    }
}