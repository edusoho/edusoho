<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeCreateRequest;

class AopTradeCreateResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_create_response';

    /**
     * @var AopTradeCreateRequest
     */
    protected $request;


    public function getTradeNo()
    {
        return $this->getAlipayResponse('trade_no');
    }


    public function getOutTradeNo()
    {
        return $this->getAlipayResponse('out_trade_no');
    }
}
