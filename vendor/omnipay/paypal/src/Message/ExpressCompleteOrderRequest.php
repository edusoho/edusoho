<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express Complete Order Request
 */
class ExpressCompleteOrderRequest extends ExpressCompleteAuthorizeRequest
{
    public function getData()
    {
        $data = parent::getData();
        $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Order';

        return $data;
    }
}
