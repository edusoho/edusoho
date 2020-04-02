<?php

namespace Omnipay\WechatPay\Message;

use GuzzleHttp\Client;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;

/**
 * Class PayBankRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2
 * @method  PayBankResponse send()
 */
class PayBankRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('mch_id', 'partner_trade_no', 'enc_bank_no', 'enc_true_name', 'bank_code', 'amount', 'desc', 'cert_path', 'key_path');

        $data = array(
            'mch_id'           => $this->getMchId(),
            'partner_trade_no' => $this->getPartnerTradeNo(),
            'enc_bank_no'      => $this->getEncBankNo(),
            'enc_true_name'    => $this->getEncTrueName(),
            'bank_code'        => $this->getBankCode(),
            'amount'           => $this->getAmount(),
            'desc'             => $this->getDesc(),
            'nonce_str'        => md5(uniqid()),
        );

        $data = array_filter($data);
        
        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }


    /**
     * @return mixed
     */
    public function getPartnerTradeNo()
    {
        return $this->getParameter('partner_trade_no');
    }


    /**
     * @param mixed $partnerTradeNo
     */
    public function setPartnerTradeNo($partnerTradeNo)
    {
        $this->setParameter('partner_trade_no', $partnerTradeNo);
    }


    /**
     * @return mixed
     */
    public function getEncBankNo()
    {
        return $this->getParameter('enc_bank_no');
    }


    /**
     * @param mixed $encBankNo
     */
    public function setEncBankNo($encBankNo)
    {
        $this->setParameter('enc_bank_no', $encBankNo);
    }


    /**
     * @return mixed
     */
    public function getEncTrueName()
    {
        return $this->getParameter('enc_true_name');
    }


    /**
     * @param mixed $encTrueName
     */
    public function setEncTrueName($encTrueName)
    {
        $this->setParameter('enc_true_name', $encTrueName);
    }


    /**
     * @return mixed
     */
    public function getBankCode()
    {
        return $this->getParameter('bank_code');
    }


    /**
     * @param mixed $bankCode
     */
    public function setBankCode($bankCode)
    {
        $this->setParameter('bank_code', $bankCode);
    }


    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->getParameter('amount');
    }


    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->setParameter('amount', $amount);
    }


    /**
     * @return mixed
     */
    public function getDesc()
    {
        return $this->getParameter('desc');
    }


    /**
     * @param mixed $desc
     */
    public function setDesc($desc)
    {
        $this->setParameter('desc', $desc);
    }


    /**
     * @return mixed
     */
    public function getCertPath()
    {
        return $this->getParameter('cert_path');
    }


    /**
     * @param mixed $certPath
     */
    public function setCertPath($certPath)
    {
        $this->setParameter('cert_path', $certPath);
    }


    /**
     * @return mixed
     */
    public function getKeyPath()
    {
        return $this->getParameter('key_path');
    }


    /**
     * @param mixed $keyPath
     */
    public function setKeyPath($keyPath)
    {
        $this->setParameter('key_path', $keyPath);
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
        $body         = Helper::array2xml($data);
        $client = new Client();
        
        $options = [
            'body'    => $body,
            'verify'  => true,
            'cert'    => $this->getCertPath(),
            'ssl_key' => $this->getKeyPath(),
        ];
        $response = $client->request('POST', $this->endpoint, $options)->getBody();
        $responseData = Helper::xml2array($response);
        
        return $this->response = new PayBankResponse($this, $responseData);
    }
}
