<?php

namespace Omnipay\WechatPay\Message;

use GuzzleHttp\Client;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;

/**
 * Class PromotionTransferRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
 * @method  PromotionTransferResponse send()
 */
class PromotionTransferRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('app_id', 'mch_id', 'partner_trade_no', 'cert_path', 'key_path');

        $data = array(
            'mch_appid'        => $this->getAppId(),
            'mchid'            => $this->getMchId(),
            'device_info'      => $this->getDeviceInfo(),     // <optional>
            'partner_trade_no' => $this->getPartnerTradeNo(),
            'openid'           => $this->getOpenId(),
            'check_name'       => $this->getCheckName(),      // <NO_CHECK or FORCE_CHECK>
            're_user_name'     => $this->getReUserName(),
            'amount'           => $this->getAmount(),
            'desc'             => $this->getDesc(),
            'spbill_create_ip' => $this->getSpbillCreateIp(),
            'nonce_str'        => md5(uniqid()),
        );

        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }


    /**
     * @return mixed
     */
    public function getDeviceInfo()
    {
        return $this->getParameter('device_Info');
    }


    /**
     * @param mixed $deviceInfo
     */
    public function setDeviceInfo($deviceInfo)
    {
        $this->setParameter('device_Info', $deviceInfo);
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
    public function getOpenId()
    {
        return $this->getParameter('open_id');
    }


    /**
     * @param mixed $openId
     */
    public function setOpenId($openId)
    {
        $this->setParameter('open_id', $openId);
    }


    /**
     * @return mixed
     */
    public function getCheckName()
    {
        return $this->getParameter('check_name');
    }


    /**
     * @param mixed $checkName
     */
    public function setCheckName($checkName)
    {
        $this->setParameter('check_name', $checkName);
    }


    /**
     * @return mixed
     */
    public function getReUserName()
    {
        return $this->getParameter('re_user_name');
    }


    /**
     * @param mixed $reUserNamme
     */
    public function setReUserName($reUserName)
    {
        $this->setParameter('re_user_name', $reUserName);
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
    public function getSpbillCreateIp()
    {
        return $this->getParameter('spbill_create_ip');
    }


    /**
     * @param mixed $spbill_create_ip
     */
    public function setSpbillCreateIp($spbillCreateIp)
    {
        $this->setParameter('spbill_create_ip', $spbillCreateIp);
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

        return $this->response = new PromotionTransferResponse($this, $responseData);
    }
}
