<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Message\AbstractResponse;
/**
 * Class CompletePurchaseResponse
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_1
 *
 */
class CompletePurchaseResponse extends \Omnipay\Common\Message\AbstractResponse
{
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->isPaid();
    }
    public function isPaid()
    {
        $data = $this->getData();
        return $data['paid'];
    }
    public function isSignMatch()
    {
        $data = $this->getData();
        return $data['sign_match'];
    }
    public function getRequestData()
    {
        return $this->request->getData();
    }
}