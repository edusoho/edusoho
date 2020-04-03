<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradePayRequest;

class AopTradePayResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_pay_response';

    /**
     * @var AopTradePayRequest
     */
    protected $request;


    public function isPayFailed()
    {
        return $this->getCode() == '40004';
    }


    public function isPaid()
    {
        return $this->getCode() == '10000';
    }


    public function isWaitPay()
    {
        return $this->getCode() == '10003';
    }


    public function isUnknownException()
    {
        return $this->getCode() == '20000';
    }
}
