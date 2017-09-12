<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;

/**
 * Class QueryOpenIdByAuthCodeRequest
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_13&index=9
 * @method QueryOpenIdByAuthCodeResponse send()
 */
class QueryOpenIdByAuthCodeRequest extends BaseAbstractRequest
{

    protected $endpoint = 'https://api.mch.weixin.qq.com/tools/authcodetoopenid';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {

        $this->validate('app_id', 'mch_id', 'auth_code');

        $data = array (
            'appid'     => $this->getAppId(),
            'mch_id'    => $this->getMchId(),
            'auth_code' => $this->getAuthCode(),
            'nonce_str' => md5(uniqid()),
        );

        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }


    /**
     * @return mixed
     */
    public function getAuthCode()
    {
        return $this->getParameter('auth_code');
    }


    /**
     * @param mixed $authCode
     */
    public function setAuthCode($authCode)
    {
        $this->setParameter('auth_code', $authCode);
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
        $request      = $this->httpClient->post($this->endpoint)->setBody(Helper::array2xml($data));
        $response     = $request->send()->getBody();
        $responseData = Helper::xml2array($response);

        return $this->response = new CloseOrderResponse($this, $responseData);
    }
}
