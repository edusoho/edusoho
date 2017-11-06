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

        if (!empty($data['req_info']['refund_status']) && $data['req_info']['refund_status'] == 'SUCCESS') {
            return true;
        }

        return false;
    }

    public function getRequestData()
    {
        return $this->request->getData();
    }
}
