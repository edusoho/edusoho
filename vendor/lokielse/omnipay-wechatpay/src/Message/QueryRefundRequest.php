<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;

/**
 * Class QueryRefundRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_5&index=7
 * @method  QueryRefundResponse send()
 */
class QueryRefundRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/pay/refundquery';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('app_id', 'mch_id');

        $queryIdEmpty = ! $this->getTransactionId() && ! $this->getOutTradeNo();
        $queryIdEmpty = ($queryIdEmpty && ! $this->getOutRefundNo() && ! $this->getRefundId());

        if ($queryIdEmpty) {
            $message = "The 'transaction_id' or 'out_trade_no' or 'out_refund_no' or 'refund_id' parameter is required";
            throw new InvalidRequestException($message);
        }

        $data = array(
            'appid'          => $this->getAppId(),
            'mch_id'         => $this->getMchId(),
            'sub_appid'      => $this->getSubAppId(),
            'sub_mch_id'     => $this->getSubMchId(),
            'device_info'    => $this->getDeviceInfo(),
            'transaction_id' => $this->getTransactionId(),
            'out_trade_no'   => $this->getOutTradeNo(),
            'out_refund_no'  => $this->getOutRefundNo(),
            'refund_id'      => $this->getRefundId(),
            'nonce_str'      => md5(uniqid()),
        );

        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

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
     * @return mixed
     */
    public function getDeviceInfo()
    {
        return $this->getParameter('device_Info');
    }


    /**
     * @param mixed $deviceInfo
     */
    public function setDeviceInfo($deviceInfo)
    {
        $this->setParameter('device_Info', $deviceInfo);
    }


    /**
     * @return mixed
     */
    public function getOutRefundNo()
    {
        return $this->getParameter('out_refund_no');
    }


    /**
     * @param mixed $outRefundNo
     */
    public function setOutRefundNo($outRefundNo)
    {
        $this->setParameter('out_refund_no', $outRefundNo);
    }


    /**
     * @return mixed
     */
    public function getRefundId()
    {
        return $this->getParameter('refund_id');
    }


    /**
     * @param mixed $refundId
     */
    public function setRefundId($refundId)
    {
        $this->setParameter('refund_id', $refundId);
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
        $request      = $this->httpClient->request('POST', $this->endpoint, [], Helper::array2xml($data));
        $response     = $request->getBody();
        $responseData = Helper::xml2array($response);

        return $this->response = new QueryRefundResponse($this, $responseData);
    }
}
