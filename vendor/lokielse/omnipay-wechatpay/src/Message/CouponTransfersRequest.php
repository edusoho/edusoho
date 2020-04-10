<?php

namespace Omnipay\WechatPay\Message;

use GuzzleHttp\Client;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;

/**
 * Class CouponTransfersRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/tools/sp_coupon.php?chapter=12_3
 * @method  CouponTransfersResponse send()
 */
class CouponTransfersRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/send_coupon';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('coupon_stock_id', 'openid_count', 'partner_trade_no', 'openid', 'app_id', 'mch_id', 'cert_path', 'key_path');

        $data = array(
            'coupon_stock_id'  => $this->getCouponStockId(),
            'openid_count'     => $this->getOpenIdCount(),
            'partner_trade_no' => $this->getPartnerTradeNo(),
            'openid'           => $this->getOpenId(),
            'appid'            => $this->getAppId(),
            'mch_id'           => $this->getMchId(),
            'nonce_str'        => md5(uniqid()),
        );
        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }

    /**
     * @return mixed
     */
    public function getCouponStockId()
    {
        return $this->getParameter('coupon_stock_id');
    }

    /**
     * @param mixed $couponStockId
     */
    public function setCouponStockId($couponStockId)
    {
        $this->setParameter('coupon_stock_id', $couponStockId);
    }

    /**
     * @return mixed
     */
    public function getOpenIdCount()
    {
        return $this->getParameter('openid_count');
    }

    /**
     * @param mixed $openidCount
     */
    public function setOpenIdCount($openIdCount)
    {
        $this->setParameter('openid_count', $openIdCount);
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
        return $this->getParameter('openid');
    }

    /**
     * @param mixed $openId
     */
    public function setOpenId($openid)
    {
        $this->setParameter('openid', $openid);
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
        $client       = new Client();

        $options = [
            'body'    => $body,
            'verify'  => true,
            'cert'    => $this->getCertPath(),
            'ssl_key' => $this->getKeyPath(),
        ];
        $response = $client->request('POST', $this->endpoint, $options)->getBody();
        $responseData = Helper::xml2array($response);

        return $this->response = new CouponTransfersResponse($this, $responseData);
    }
}
