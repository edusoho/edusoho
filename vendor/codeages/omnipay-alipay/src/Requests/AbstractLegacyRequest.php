<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

abstract class AbstractLegacyRequest extends AbstractRequest
{
    protected $endpoint = 'https://mapi.alipay.com/gateway.do';

    protected $service;

    protected $key;

    protected $signType;

    protected $privateKey;

    protected $alipayPublicKey;


    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
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
     * @return mixed
     */
    public function getInputCharset()
    {
        return $this->getParameter('_input_charset');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setInputCharset($value)
    {
        return $this->setParameter('_input_charset', $value);
    }


    /**
     * @return mixed
     */
    public function getAlipaySdk()
    {
        return $this->getParameter('alipay_sdk');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlipaySdk($value)
    {
        return $this->setParameter('alipay_sdk', $value);
    }


    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->getParameter('payment_type');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPaymentType($value)
    {
        return $this->setParameter('payment_type', $value);
    }


    /**
     * @return mixed
     */
    public function getSignType()
    {
        return $this->signType;
    }


    /**
     * @param $value
     *
     * @return $this
     * @throws InvalidRequestException
     */
    public function setSignType($value)
    {
        $this->signType = $value;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getAlipayPublicKey()
    {
        return $this->alipayPublicKey;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlipayPublicKey($value)
    {
        $this->alipayPublicKey = $value;

        return $this;
    }


    protected function validateOne()
    {
        $keys = func_get_args();

        $allEmpty = true;

        foreach ($keys as $key) {
            $value = $this->parameters->get($key);

            if (! empty($value)) {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            throw new InvalidRequestException(
                sprintf('The parameters (%s) must provide one at least', implode(',', $keys))
            );
        }
    }


    protected function sign($params, $signType)
    {
        $signer = new Signer($params);

        $signType = strtoupper($signType);

        if ($signType == 'MD5') {
            if (! $this->getKey()) {
                throw new InvalidRequestException('The `key` is required for `MD5` sign_type');
            }

            $sign = $signer->signWithMD5($this->getKey());
        } elseif ($signType == 'RSA') {
            if (! $this->getPrivateKey()) {
                throw new InvalidRequestException('The `private_key` is required for `RSA` sign_type');
            }

            $sign = $signer->signWithRSA($this->getPrivateKey());
        } else {
            throw new InvalidRequestException('The signType is not allowed');
        }

        return $sign;
    }


    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setKey($value)
    {
        $this->key = $value;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPrivateKey($value)
    {
        $this->privateKey = $value;

        return $this;
    }


    protected function filter($data)
    {
        return array_filter($data, 'strlen');
    }
}
