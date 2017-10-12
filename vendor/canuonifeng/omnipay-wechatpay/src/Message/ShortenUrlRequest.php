<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;
/**
 * Class ShortenUrlRequest
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_9&index=8
 * @method ShortenUrlResponse send()
 */
class ShortenUrlRequest extends \Omnipay\WechatPay\Message\BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/tools/shorturl';
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('app_id', 'mch_id', 'long_url');
        $data = array('appid' => $this->getAppId(), 'mch_id' => $this->getMchId(), 'long_url' => $this->getLongUrl(), 'nonce_str' => md5(uniqid()));
        $data = array_filter($data);
        $data['sign'] = \Omnipay\WechatPay\Helper::sign($data, $this->getApiKey());
        return $data;
    }
    /**
     * @return mixed
     */
    public function getLongUrl()
    {
        return $this->getParameter('long_url');
    }
    /**
     * @param mixed $longUrl
     */
    public function setLongUrl($longUrl)
    {
        $this->setParameter('long_url', $longUrl);
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
        return $this->response = new \Omnipay\WechatPay\Message\ShortenUrlResponse($this, $responseData);
    }
}