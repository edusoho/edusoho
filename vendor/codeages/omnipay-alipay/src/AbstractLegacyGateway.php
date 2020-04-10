<?php

namespace Omnipay\Alipay;

use Omnipay\Alipay\Requests\LegacyCloseRequest;
use Omnipay\Alipay\Requests\LegacyCompletePurchaseRequest;
use Omnipay\Alipay\Requests\LegacyCompleteRefundRequest;
use Omnipay\Alipay\Requests\LegacyQueryRequest;
use Omnipay\Alipay\Requests\LegacyRefundRequest;
use Omnipay\Common\AbstractGateway;

abstract class AbstractLegacyGateway extends AbstractGateway
{
    public function getDefaultParameters()
    {
        return [
            'inputCharset' => 'UTF-8',
            'signType'     => 'MD5',
            'paymentType'  => '1',
            'alipaySdk'    => 'lokielse/omnipay-alipay',
        ];
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
    public function getSignType()
    {
        return $this->getParameter('sign_type');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSignType($value)
    {
        return $this->setParameter('sign_type', $value);
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
    public function getKey()
    {
        return $this->getParameter('key');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setKey($value)
    {
        return $this->setParameter('key', $value);
    }


    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->getParameter('private_key');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPrivateKey($value)
    {
        return $this->setParameter('private_key', $value);
    }


    /**
     * @return mixed
     */
    public function getAlipayPublicKey()
    {
        return $this->getParameter('alipay_public_key');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlipayPublicKey($value)
    {
        return $this->setParameter('alipay_public_key', $value);
    }


    /**
     * @return mixed
     */
    public function getSellerId()
    {
        return $this->getParameter('seller_id');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSellerId($value)
    {
        return $this->setParameter('seller_id', $value);
    }


    /**
     * @return mixed
     */
    public function getSellerEmail()
    {
        return $this->getParameter('seller_email');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSellerEmail($value)
    {
        return $this->setParameter('seller_email', $value);
    }


    /**
     * @return mixed
     */
    public function getSellerAccountName()
    {
        return $this->getParameter('seller_account_name');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSellerAccountName($value)
    {
        return $this->setParameter('seller_account_name', $value);
    }


    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setNotifyUrl($value)
    {
        return $this->setParameter('notify_url', $value);
    }


    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->getParameter('return_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('return_url', $value);
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
    public function getItBPay()
    {
        return $this->getParameter('it_b_pay');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setItBPay($value)
    {
        return $this->setParameter('it_b_pay', $value);
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
     * @param array $parameters
     *
     * @return \Omnipay\Alipay\Requests\LegacyCompletePurchaseRequest
     */
    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest(LegacyCompletePurchaseRequest::class, $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\Alipay\Requests\LegacyRefundRequest
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest(LegacyRefundRequest::class, $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\Alipay\Requests\LegacyRefundRequest
     */
    public function completeRefund(array $parameters = [])
    {
        return $this->createRequest(LegacyCompleteRefundRequest::class, $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return \Omnipay\Alipay\Requests\LegacyQueryRequest
     */
    public function query(array $parameters = [])
    {
        return $this->createRequest(LegacyQueryRequest::class, $parameters);
    }

    public function close(array $parameters = [])
    {
        $request = new LegacyCloseRequest(array('key' => $this->getPartner(), 'secret' => $this->getKey()));
        $request->setParams($parameters);

        return $request;
    }
}
