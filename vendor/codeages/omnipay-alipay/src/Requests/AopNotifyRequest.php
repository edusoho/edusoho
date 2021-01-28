<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\Responses\AopNotifyResponse;
use Omnipay\Alipay\Responses\VerifyNotifyIdResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class AopNotifyRequest extends AbstractAopRequest
{

    /**
     * @var ParameterBag
     */
    public $params;

    protected $verifyNotifyId = false;

    protected $sort = true;

    protected $encodePolicy = 'QUERY';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->initParams();

        $this->validateParams();

        return $this->params->all();
    }


    /**
     * @return array|mixed
     */
    private function initParams()
    {
        $params = $this->getParams();

        if (! $params) {
            $params = array_merge($_GET, $_POST);
        }

        $this->params = new ParameterBag($params);

        return $params;
    }


    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->getParameter('params');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setParams($value)
    {
        return $this->setParameter('params', $value);
    }


    public function validateParams()
    {
        if (empty($this->params->all())) {
            throw new InvalidRequestException('The `params` or $_REQUEST is empty');
        }

        if (! $this->params->has('sign_type')) {
            throw new InvalidRequestException('The `sign_type` is required');
        }

        if (! $this->params->has('sign')) {
            throw new InvalidRequestException('The `sign` is required');
        }
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
        $this->verifySignature();

        if ($this->params->has('notify_id')) {
            if ($this->verifyNotifyId) {
                $this->verifyNotifyId();
            }
        }

        return $this->response = new AopNotifyResponse($this, $data);
    }


    protected function verifySignature()
    {
        $signer = new Signer($this->params->all());
        $signer->setSort($this->sort);
        $signer->setEncodePolicy($this->encodePolicy);
        $content = $signer->getContentToSign();

        $sign = $this->params->get('sign');
        $signType = $this->params->get('sign_type');
        
        if ($signType == 'RSA2') {
            $match = (new Signer)->verifyWithRSA($content, $sign, $this->getAlipayPublicKey(), OPENSSL_ALGO_SHA256);
        } else {
            $match = (new Signer)->verifyWithRSA($content, $sign, $this->getAlipayPublicKey());
        }

        if (! $match) {
            throw new InvalidRequestException('The signature is not match');
        }
    }


    protected function verifyNotifyId()
    {
        if (! $this->getPartner()) {
            throw new InvalidRequestException('The partner is required for notify_id verify');
        }

        $request = new LegacyVerifyNotifyIdRequest($this->httpClient, $this->httpRequest);
        $request->setPartner($this->getPartner());
        $request->setNotifyId($this->params->get('notify_id'));

        /**
         * @var VerifyNotifyIdResponse $response
         */
        $response = $request->send();

        if (! $response->isSuccessful()) {
            throw new InvalidRequestException('The notify_id is not trusted');
        }
    }


    /**
     * @return mixed
     */
    public function getPartner()
    {
        return $this->getParameter('partner');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPartner($value)
    {
        return $this->setParameter('partner', $value);
    }


    /**
     * @param boolean $value
     *
     * @return \Omnipay\Alipay\Requests\AopNotifyRequest
     */
    public function setVerifyNotifyId($value)
    {
        $this->verifyNotifyId = $value;

        return $this;
    }


    /**
     * @param boolean $sort
     *
     * @return \Omnipay\Alipay\Requests\AopNotifyRequest
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }


    /**
     * @param string $encodePolicy
     *
     * @return \Omnipay\Alipay\Requests\AopNotifyRequest
     */
    public function setEncodePolicy($encodePolicy)
    {
        $this->encodePolicy = $encodePolicy;

        return $this;
    }
}
