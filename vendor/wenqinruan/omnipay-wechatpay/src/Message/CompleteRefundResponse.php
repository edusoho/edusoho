<?php

namespace Omnipay\WechatPay\Message;

use Omnipay\Common\Message\AbstractResponse;

class CompleteRefundResponse extends AbstractResponse
{

    public function isSuccessful()
    {
        return $this->isRefunded();
    }

    public function isRefunded()
    {
        $data = $this->getData();

        return $data['refunded'];
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
