<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\WechatPay\Helper;

/**
 * Class DownloadBillRequest
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_6&index=8
 * @method DownloadBillResponse send()
 */
class DownloadBillRequest extends BaseAbstractRequest
{

    protected $endpoint = 'https://api.mch.weixin.qq.com/pay/downloadbill';


    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('app_id', 'mch_id', 'bill_date');

        $data = array (
            'appid'       => $this->getAppId(),
            'mch_id'      => $this->getMchId(),
            'device_info' => $this->getDeviceInfo(),
            'bill_date'   => $this->getBillDate(),
            'bill_type'   => $this->getBillType(),//<>
            'nonce_str'   => md5(uniqid()),
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
    public function getBillDate()
    {
        return $this->getParameter('bill_date');
    }


    /**
     * @param mixed $billDate
     */
    public function setBillDate($billDate)
    {
        $this->setParameter('bill_date', $billDate);
    }


    /**
     * @return mixed
     */
    public function getBillType()
    {
        return $this->getParameter('bill_type');
    }


    /**
     * @param mixed $billType
     */
    public function setBillType($billType)
    {
        $this->setParameter('bill_type', $billType);
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
        $responseData = $this->post($this->endpoint, $data, 120);

        return $this->response = new DownloadBillResponse($this, $responseData);
    }


    private function post($url, $data = array (), $timeout = 3)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Helper::array2xml($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        if (preg_match('#return_code#', $result)) {
            $result = Helper::xml2array($result);
        } else {
            $result = array (['return_code' => 'SUCCESS', 'raw' => $result]);
        }

        return $result;
    }
}
