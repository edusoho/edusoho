<?php

namespace Omnipay\WechatPay\Message;

use GuzzleHttp\Client;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;

/**
 * Class RefundOrderRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_4&index=6
 * @method  RefundOrderResponse send()
 */
class RefundOrderRequest extends BaseAbstractRequest
{
    protected $endpoint = 'https://api.mch.weixin.qq.com/secapi/pay/refund';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('app_id', 'mch_id', 'out_trade_no', 'cert_path', 'key_path');

        $data = [
            'appid'           => $this->getAppId(),
            'mch_id'          => $this->getMchId(),
            'sub_appid'       => $this->getSubAppId(),
            'sub_mch_id'      => $this->getSubMchId(),
            'device_info'     => $this->getDeviceInfo(),//<>
            'transaction_id'  => $this->getTransactionId(),
            'out_trade_no'    => $this->getOutTradeNo(),
            'out_refund_no'   => $this->getOutRefundNo(),
            'total_fee'       => $this->getTotalFee(),
            'refund_fee'      => $this->getRefundFee(),
            'refund_fee_type' => $this->getRefundFee(),//<>
            'op_user_id'      => $this->getOpUserId() ?: $this->getMchId(),
            'refund_account'  => $this->getRefundAccount(),
            'nonce_str'       => md5(uniqid()),
        ];

        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }

    /**
     * @return mixed
     */
    public function getRefundAccount()
    {
        return $this->getParameter('refund_account');
    }

    /**
     * @param mixed $refundAccount
     */
    public function setRefundAccount($refundAccount)
    {
        $this->setParameter('refund_account', $refundAccount);
    }


    /**
     * @return mixed
     */
    public function getOutTradeNo()
    {
        return $this->getParameter('out_trade_no');
    }


    /**
     * @param mixed $outTradeNo
     */
    public function setOutTradeNo($outTradeNo)
    {
        $this->setParameter('out_trade_no', $outTradeNo);
    }


    /**
     * @return mixed
     */
    public function getOpUserId()
    {
        return $this->getParameter('op_user_id');
    }


    /**
     * @param mixed $opUserId
     */
    public function setOpUserId($opUserId)
    {
        $this->setParameter('op_user_id', $opUserId);
    }


    /**
     * @return mixed
     */
    public function getOutRefundNo()
    {
        return $this->getParameter('out_refund_no');
    }


    /**
     * @param mixed $outRefundNo
     */
    public function setOutRefundNo($outRefundNo)
    {
        $this->setParameter('out_refund_no', $outRefundNo);
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
    public function getTransactionId()
    {
        return $this->getParameter('transaction_id');
    }


    public function setTransactionId($transactionId)
    {
        $this->setParameter('transaction_id', $transactionId);
    }


    /**
     * @return mixed
     */
    public function getTotalFee()
    {
        return $this->getParameter('total_fee');
    }


    /**
     * @param mixed $totalFee
     */
    public function setTotalFee($totalFee)
    {
        $this->setParameter('total_fee', $totalFee);
    }


    /**
     * @return mixed
     */
    public function getRefundFee()
    {
        return $this->getParameter('refund_fee');
    }


    /**
     * @param mixed $refundFee
     */
    public function setRefundFee($refundFee)
    {
        $this->setParameter('refund_fee', $refundFee);
    }


    /**
     * @return mixed
     */
    public function getRefundType()
    {
        return $this->getParameter('refund_fee_type');
    }


    /**
     * @param mixed $refundFeeType
     */
    public function setRefundType($refundFeeType)
    {
        $this->setParameter('refund_fee_type', $refundFeeType);
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendData($data)
    {
        $body = Helper::array2xml($data);

        $client = new Client();

        $options = [
            'body'    => $body,
            'verify'  => true,
            'cert'    => $this->getCertPath(),
            'ssl_key' => $this->getKeyPath(),
        ];

        $result = $client->request('POST', $this->endpoint, $options)->getBody()->getContents();

        $responseData = Helper::xml2array($result);

        return $this->response = new RefundOrderResponse($this, $responseData);
    }
}
