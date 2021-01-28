<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\Responses\AopNotifyResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopVerifyAppPayReturnRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=193&articleId=105302&docType=1
 */
class AopVerifyAppPayReturnRequest extends AbstractAopRequest
{
    protected $key = 'alipay_trade_app_pay_response';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate();

        $result = $this->getResult();

        if (substr($result, 0, 3) == '{\"') {
            $result = stripslashes($result);
        }

        $response = json_decode($result, true);

        $data              = $response[$this->key];
        $data['sign']      = $response['sign'];
        $data['sign_type'] = $response['sign_type'];

        return $data;
    }


    /**
     * @throws InvalidRequestException
     */
    public function validate()
    {
        parent::validate(
            'result'
        );

        $result = $this->getResult();

        if (! is_string($result)) {
            throw new InvalidRequestException('The result should be string');
        }

        if (substr($result, 0, 3) == '{\"') {
            $result = stripslashes($result);
        }

        $data = json_decode($result, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new InvalidRequestException('The result should be a valid json string');
        }

        if (! isset($data[$this->key])) {
            throw new InvalidRequestException("The result decode data should contain {$this->key}");
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
     * @param $value
     *
     * @return $this
     */
    public function setResult($value)
    {
        return $this->setParameter('result', $value);
    }


    /**
     * @return mixed
     */
    public function getMemo()
    {
        return $this->getParameter('memo');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setMemo($value)
    {
        return $this->setParameter('memo', $value);
    }


    /**
     * @return mixed
     */
    public function getResultStatus()
    {
        return $this->getParameter('resultStatus');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setResultStatus($value)
    {
        return $this->setParameter('resultStatus', $value);
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
        $request = new AopNotifyRequest($this->httpClient, $this->httpRequest);
        $request->initialize($this->parameters->all());
        $request->setEndpoint($this->getEndpoint());
        $request->setParams($data);
        $request->setSort(false);
        $request->setEncodePolicy(Signer::ENCODE_POLICY_JSON);
        $request->setAlipayPublicKey($this->getAlipayPublicKey());

        /**
         * @var AopNotifyResponse $response
         */
        $response = $request->send();

        return $response;
    }
}
