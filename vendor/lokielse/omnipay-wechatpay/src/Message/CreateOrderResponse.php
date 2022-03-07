<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\WechatPay\Helper;

/**
 * Class CreateOrderResponse
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_1
 */
class CreateOrderResponse extends BaseAbstractResponse
{

    /**
     * @var CreateOrderRequest
     */
    protected $request;


    public function getAppOrderData()
    {
        if ($this->isSuccessful()) {
            $data = [
                'appid'     => $this->request->getAppId(),
                'partnerid' => $this->request->getMchId(),
                'prepayid'  => $this->getPrepayId(),
                'package'   => 'Sign=WXPay',
                'noncestr'  => md5(uniqid()),
                'timestamp' => time(),
            ];

            $data['sign'] = Helper::sign($data, $this->request->getApiKey());
        } else {
            $data = null;
        }

        return $data;
    }


    public function getPrepayId()
    {
        if ($this->isSuccessful()) {
            $data = $this->getData();

            return $data['prepay_id'];
        } else {
            return null;
        }
    }


    public function getJsOrderData()
    {
        if ($this->isSuccessful()) {
            $data = [
                'appId'     => $this->request->getAppId(),
                'package'   => 'prepay_id=' . $this->getPrepayId(),
                'nonceStr'  => md5(uniqid()),
                'timeStamp' => time() . '',
            ];

            $data['signType'] = 'MD5';
            $data['paySign']  = Helper::sign($data, $this->request->getApiKey());
        } else {
            $data = null;
        }

        return $data;
    }


    public function getCodeUrl()
    {
        if ($this->isSuccessful() && $this->request->getTradeType() == 'NATIVE') {
            $data = $this->getData();

            return $data['code_url'];
        } else {
            return null;
        }
    }


    public function getMwebUrl()
    {
        if ($this->isSuccessful() && $this->request->getTradeType() == 'MWEB') {
            $data = $this->getData();

            return $data['mweb_url'];
        } else {
            return null;
        }
    }
}
