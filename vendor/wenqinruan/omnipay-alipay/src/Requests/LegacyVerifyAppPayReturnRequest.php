<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\LegacyNotifyResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
/**
 * Class LegacyVerifyAppPayReturnRequest
 * @package Omnipay\Alipay\Requests
 */
class LegacyVerifyAppPayReturnRequest extends \Omnipay\Alipay\Requests\AbstractLegacyRequest
{
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validateParams();
        $result = trim($this->getResult());
        if (substr($result, -2, 2) == '\\"') {
            $result = stripslashes($result);
        }
        parse_str($result, $data);
        $sign = trim($data['sign'], '"');
        $sign = str_replace(' ', '+', $sign);
        $signType = trim($data['sign_type'], '"');
        $data['sign'] = $sign;
        $data['sign_type'] = $signType;
        return $data;
    }
    /**
     * @throws InvalidRequestException
     */
    public function validateParams()
    {
        $this->validate('result');
        $result = $this->getResult();
        if (!is_string($result)) {
            throw new \Omnipay\Common\Exception\InvalidRequestException('The result should be string');
        }
        parse_str($result, $data);
        if (!isset($data['sign'])) {
            throw new \Omnipay\Common\Exception\InvalidRequestException('The `result` is invalid');
        }
        if (!isset($data['sign_type'])) {
            throw new \Omnipay\Common\Exception\InvalidRequestException('The `result` is invalid');
        }
    }
    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->getParameter('result');
    }
    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $request = new \Omnipay\Alipay\Requests\LegacyNotifyRequest($this->httpClient, $this->httpRequest);
        $request->initialize($this->parameters->all());
        $request->setParams($data);
        $request->setSort(false);
        $request->setAlipayPublicKey($this->getAlipayPublicKey());
        /**
         * @var LegacyNotifyResponse $response
         */
        $response = $request->send();
        return $response;
    }
    /**
     * @param $value
     *
     * @return $this
     */
    public function setResult($value)
    {
        return $this->setParameter('result', $value);
    }
}