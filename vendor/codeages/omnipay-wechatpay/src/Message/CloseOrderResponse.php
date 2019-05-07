<?php

namespace Omnipay\WechatPay\Message;

/**
 * Class CloseOrderResponse
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_3&index=5
 */
class CloseOrderResponse extends \Omnipay\WechatPay\Message\BaseAbstractResponse
{
    public function getFailData()
    {
        $data = $this->getData();
        if ($data['result_code'] == 'FAIL') {
            return '错误码：'.$data['err_code'].'，错误原因：'.$data['err_code_des'];
        }
        return null;
    }

    public function getRequestData()
    {
        return $this->request->getData();
    }
}