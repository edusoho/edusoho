<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\LegacyWapPurchaseResponse;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class LegacyWapPurchaseRequest
 * @package   Omnipay\Alipay\Requests
 * @link      https://doc.open.alipay.com/docs/doc.htm?treeId=60&articleId=104790&docType=1
 */
class LegacyWapPurchaseRequest extends AbstractLegacyRequest
{

    protected $service = 'alipay.wap.create.direct.pay.by.user';

    protected $key;

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

        $data = $this->filter($this->parameters->all());

        $data['service']   = $this->service;
        $data['sign']      = $this->sign($data, $this->getSignType());
        $data['sign_type'] = $this->getSignType();

        return $data;
    }


    protected function validateParams()
    {
        $this->validate(
            'partner',
            '_input_charset',
            'sign_type',
            'out_trade_no',
            'subject',
            'total_fee',
            'seller_id',
            'payment_type'
        );
    }


    /**
     * @return mixed
     */
    public function getSignType()
    {
        return $this->getParameter('sign_type');
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
        return $this->response = new LegacyWapPurchaseResponse($this, $data);
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
    public function getShowUrl()
    {
        return $this->getParameter('show_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setShowUrl($value)
    {
        return $this->setParameter('show_url', $value);
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
    public function getOtherfee()
    {
        return $this->getParameter('otherfee');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setOtherfee($value)
    {
        return $this->setParameter('otherfee', $value);
    }


    /**
     * @return mixed
     */
    public function getAirticket()
    {
        return $this->getParameter('airticket');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAirticket($value)
    {
        return $this->setParameter('airticket', $value);
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
    public function getBuyerCertNo()
    {
        return $this->getParameter('buyer_cert_no');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBuyerCertNo($value)
    {
        return $this->setParameter('buyer_cert_no', $value);
    }


    /**
     * @return mixed
     */
    public function getBuyerRealName()
    {
        return $this->getParameter('buyer_real_name');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBuyerRealName($value)
    {
        return $this->setParameter('buyer_real_name', $value);
    }


    /**
     * @return mixed
     */
    public function getScene()
    {
        return $this->getParameter('scene');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setScene($value)
    {
        return $this->setParameter('scene', $value);
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
    public function getAppPay()
    {
        return $this->getParameter('app_pay');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppPay($value)
    {
        return $this->setParameter('app_pay', $value);
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
}
