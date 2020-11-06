<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express Complete Purchase Request
 */
class ExpressCompletePurchaseRequest extends ExpressCompleteAuthorizeRequest
{
    public function getData()
    {
        $data = parent::getData();
        $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';

        return $data;
    }

    protected function createResponse($data)
    {
        return $this->response = new ExpressCompletePurchaseResponse($this, $data);
    }
}
