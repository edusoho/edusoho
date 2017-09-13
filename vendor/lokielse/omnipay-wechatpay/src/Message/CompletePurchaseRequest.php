<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;
/**
 *
 * Class CompletePurchaseRequest
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_7&index=3
 * @method CompletePurchaseResponse send()
 */
class CompletePurchaseRequest extends \Omnipay\WechatPay\Message\BaseAbstractRequest
{
    public function setRequestParams($requestParams)
    {
        $this->setParameter('request_params', $requestParams);
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
        $data = $this->getData();
        $sign = \Omnipay\WechatPay\Helper::sign($data, $this->getApiKey());
        $responseData = array();
        if (isset($data['sign']) && $data['sign'] && $sign === $data['sign']) {
            $responseData['sign_match'] = true;
        } else {
            $responseData['sign_match'] = false;
        }
        if ($responseData['sign_match'] && isset($data['result_code']) && $data['result_code'] == 'SUCCESS') {
            $responseData['paid'] = true;
        } else {
            $responseData['paid'] = false;
        }
        return $this->response = new \Omnipay\WechatPay\Message\CompletePurchaseResponse($this, $responseData);
    }
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $data = $this->getRequestParams();
        if (is_string($data)) {
            $data = \Omnipay\WechatPay\Helper::xml2array($data);
        }
        return $data;
    }
    public function getRequestParams()
    {
        return $this->getParameter('request_params');
    }
}