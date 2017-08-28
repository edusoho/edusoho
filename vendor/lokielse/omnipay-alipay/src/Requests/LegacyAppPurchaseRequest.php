<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Common\Signer;
use Omnipay\Alipay\Responses\LegacyAppPurchaseResponse;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class LegacyAppPurchaseRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=59&articleId=103663&docType=1
 */
class LegacyAppPurchaseRequest extends AbstractLegacyRequest
{

    protected $service = 'mobile.securitypay.pay';

    protected $privateKey;


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->validateParams();

        $params = $this->getParamsToSign();

        $signer = new Signer($params);
        $sign   = $signer->signWithRSA($this->privateKey);

        $resp['order_string'] = sprintf(
            '%s&sign="%s"&sign_type="RSA"',
            $signer->getContentToSign(),
            urlencode($sign)
        );

        return $resp;
    }


    protected function validateParams()
    {
        $this->validate(
            'partner',
            '_input_charset',
            'sign_type',
            'notify_url',
            'out_trade_no',
            'subject',
            'total_fee',
            'payment_type'
        );
    }


    private function getParamsToSign()
    {
        $params            = $this->parameters->all();
        $params['service'] = $this->service;

        $params = array_filter($params, 'strlen');

        $params = array_map(
            function ($v) {
                return sprintf('"%s"', $v);
            },
            $params
        );

        return $params;
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
        return $this->response = new LegacyAppPurchaseResponse($this, $data);
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
    public function getAppId()
    {
        return $this->getParameter('app_id');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppId($value)
    {
        return $this->setParameter('app_id', $value);
    }


    /**
     * @return mixed
     */
    public function getAppEnv()
    {
        return $this->getParameter('appenv');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppEnv($value)
    {
        return $this->setParameter('appenv', $value);
    }


    /**
     * @return mixed
     */
    public function getOutTradeNo()
    {
        return $this->getParameter('out_trade_no');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setOutTradeNo($value)
    {
        return $this->setParameter('out_trade_no', $value);
    }


    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->getParameter('subject');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setSubject($value)
    {
        return $this->setParameter('subject', $value);
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
    public function getTotalFee()
    {
        return $this->getParameter('total_fee');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setTotalFee($value)
    {
        return $this->setParameter('total_fee', $value);
    }


    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->getParameter('body');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBody($value)
    {
        return $this->setParameter('body', $value);
    }


    /**
     * @return mixed
     */
    public function getGoodsType()
    {
        return $this->getParameter('goods_type');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setGoodsType($value)
    {
        return $this->setParameter('goods_type', $value);
    }


    /**
     * @return mixed
     */
    public function getHbFqParam()
    {
        return $this->getParameter('hb_fq_param');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setHbFqParam($value)
    {
        return $this->setParameter('hb_fq_param', $value);
    }


    /**
     * @return mixed
     */
    public function getRnCheck()
    {
        return $this->getParameter('rn_check');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setRnCheck($value)
    {
        return $this->setParameter('rn_check', $value);
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
    public function getExternToken()
    {
        return $this->getParameter('extern_token');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setExternToken($value)
    {
        return $this->setParameter('extern_token', $value);
    }


    /**
     * @return mixed
     */
    public function getPromoParams()
    {
        return $this->getParameter('promo_params');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPromoParams($value)
    {
        return $this->setParameter('promo_params', $value);
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
        return $this->privateKey = $value;
    }
}
