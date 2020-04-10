<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\Responses\LegacyNotifyResponse;
use Omnipay\Alipay\Responses\VerifyNotifyIdResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class LegacyNotifyRequest extends AbstractLegacyRequest
{

    /**
     * @var ParameterBag
     */
    public $params;

    protected $verifyNotifyId = true;

    protected $sort = true;


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
            throw new InvalidRequestException('The sign_type is required');
        }

        if (! $this->params->has('sign')) {
            throw new InvalidRequestException('The sign is required');
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

        return $this->response = new LegacyNotifyResponse($this, $data);
    }


    protected function verifySignature()
    {
        $signer = new Signer($this->params->all());
        $signer->setSort($this->sort);
        $content = $signer->getContentToSign();

        $sign     = $this->params->get('sign');
        $signType = strtoupper($this->params->get('sign_type'));

        if ($signType == 'MD5') {
            if (! $this->getKey()) {
                throw new InvalidRequestException('The `key` is required for `MD5` sign_type');
            }

            $match = (new Signer)->verifyWithMD5($content, $sign, $this->getKey());
        } elseif ($signType == 'RSA') {
            if (! $this->getAlipayPublicKey()) {
                throw new InvalidRequestException('The `alipay_public_key` is required for `RSA` sign_type');
            }

            $match = (new Signer)->verifyWithRSA($content, $sign, $this->getAlipayPublicKey());
        } else {
            throw new InvalidRequestException('The `sign_type` is invalid');
        }

        if (! $match) {
            throw new InvalidRequestException('The signature is not match');
        }
    }


    protected function verifyNotifyId()
    {
        $request = new LegacyVerifyNotifyIdRequest($this->httpClient, $this->httpRequest);
        $request->initialize($this->parameters->all());
        $request->setPartner($this->getPartner());
        $request->setNotifyId($this->params->get('notify_id'));

        /**
         * @var VerifyNotifyIdResponse $response
         */
        $response = $request->send();

        if (! $response->isSuccessful()) {
            throw new InvalidRequestException('The `notify_id` verify failed, which TTL is 60s');
        }
    }


    /**
     * @param boolean $verifyNotifyId
     *
     * @return $this
     */
    public function setVerifyNotifyId($verifyNotifyId)
    {
        $this->verifyNotifyId = $verifyNotifyId;

        return $this;
    }


    /**
     * @param boolean $sort
     *
     * @return \Omnipay\Alipay\Requests\LegacyNotifyRequest
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }
}
