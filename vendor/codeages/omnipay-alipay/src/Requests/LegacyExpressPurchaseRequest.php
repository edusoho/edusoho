<?php

namespace Omnipay\Alipay\Requests;

use Omnipay\Alipay\Responses\LegacyExpressPurchaseResponse;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Class LegacyExpressPurchaseRequest
 * @package Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/doc.htm?treeId=108&articleId=104743&docType=1
 */
class LegacyExpressPurchaseRequest extends AbstractLegacyRequest
{
    protected $service = 'create_direct_pay_by_user';


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
            '_input_charset',
            'out_trade_no',
            'subject',
            'payment_type',
            'total_fee'
        );

        $this->validateOne(
            'seller_id',
            'seller_email',
            'seller_account_name'
        );
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
        return $this->response = new LegacyExpressPurchaseResponse($this, $data);
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
    public function getBuyerId()
    {
        return $this->getParameter('buyer_id');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBuyerId($value)
    {
        return $this->setParameter('buyer_id', $value);
    }


    /**
     * @return mixed
     */
    public function getBuyerEmail()
    {
        return $this->getParameter('buyer_email');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBuyerEmail($value)
    {
        return $this->setParameter('buyer_email', $value);
    }


    /**
     * @return mixed
     */
    public function getBuyerAccountName()
    {
        return $this->getParameter('buyer_account_name');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setBuyerAccountName($value)
    {
        return $this->setParameter('buyer_account_name', $value);
    }


    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->getParameter('price');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPrice($value)
    {
        return $this->setParameter('price', $value);
    }


    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->getParameter('quantity');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setQuantity($value)
    {
        return $this->setParameter('quantity', $value);
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
    public function getPayMethod()
    {
        return $this->getParameter('paymethod');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setPayMethod($value)
    {
        return $this->setParameter('paymethod', $value);
    }


    /**
     * @return mixed
     */
    public function getEnablePayMethod()
    {
        return $this->getParameter('enable_paymethod');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setEnablePayMethod($value)
    {
        return $this->setParameter('enable_paymethod', $value);
    }


    /**
     * @return mixed
     */
    public function getAntiPhishingKey()
    {
        return $this->getParameter('anti_phishing_key');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAntiPhishingKey($value)
    {
        return $this->setParameter('anti_phishing_key', $value);
    }


    /**
     * @return mixed
     */
    public function getExterInvokeIp()
    {
        return $this->getParameter('exter_invoke_ip');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setExterInvokeIp($value)
    {
        return $this->setParameter('exter_invoke_ip', $value);
    }


    /**
     * @return mixed
     */
    public function getExtraCommonParam()
    {
        return $this->getParameter('extra_common_param');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setExtraCommonParam($value)
    {
        return $this->setParameter('extra_common_param', $value);
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
    public function getToken()
    {
        return $this->getParameter('token');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }


    /**
     * @return mixed
     */
    public function getQrPayMode()
    {
        return $this->getParameter('qr_pay_mode');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setQrPayMode($value)
    {
        return $this->setParameter('qr_pay_mode', $value);
    }


    /**
     * @return mixed
     */
    public function getQrcodeWidth()
    {
        return $this->getParameter('qrcode_width');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setQrcodeWidth($value)
    {
        return $this->setParameter('qrcode_width', $value);
    }


    /**
     * @return mixed
     */
    public function getNeedBuyerRealnamed()
    {
        return $this->getParameter('need_buyer_realnamed');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setNeedBuyerRealnamed($value)
    {
        return $this->setParameter('need_buyer_realnamed', $value);
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
    public function getDefaultbank()
    {
        return $this->getParameter('defaultbank');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setDefaultbank($value)
    {
        return $this->setParameter('defaultbank', $value);
    }
}
