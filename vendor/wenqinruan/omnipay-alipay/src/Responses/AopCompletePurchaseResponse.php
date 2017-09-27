<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopCompletePurchaseRequest;
class AopCompletePurchaseResponse extends \Omnipay\Alipay\Responses\AbstractResponse
{
    /**
     * @var AopCompletePurchaseRequest
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
    public function isPaid()
    {
        if (array_get($this->data, 'trade_status')) {
            if (array_get($this->data, 'trade_status') == 'TRADE_SUCCESS') {
                return true;
            } elseif (array_get($this->data, 'trade_status') == 'TRADE_FINISHED') {
                return true;
            } else {
                return false;
            }
        } elseif (array_get($this->data, 'code') == '10000') {
            return true;
        } else {
            return false;
        }
    }
}