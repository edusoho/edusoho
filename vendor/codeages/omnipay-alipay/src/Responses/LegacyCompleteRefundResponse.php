<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\LegacyCompletePurchaseRequest;

class LegacyCompleteRefundResponse extends AbstractLegacyResponse
{

    /**
     * @var LegacyCompletePurchaseRequest
     */
    protected $request;


    public function getResponseText()
    {
        if ($this->isSuccessful()) {
            return 'success';
        } else {
            return 'fail';
        }
    }


    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return true;
    }
}
