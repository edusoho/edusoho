<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express Void Request
 */
class ExpressVoidRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('transactionReference');
        $data = $this->getBaseData();
        $data['METHOD'] = 'DoVoid';
        $data['AUTHORIZATIONID'] = $this->getTransactionReference();
        return $data;
    }
}
