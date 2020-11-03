<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express In-Context Order Request
 */
class ExpressInContextOrderRequest extends ExpressInContextAuthorizeRequest
{
    public function getData()
    {
        $data = parent::getData();
        $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Order';

        return $data;
    }
}
