<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeQueryRequest;

class AopTradeQueryResponse extends AbstractAopResponse
{

    protected $key = 'alipay_trade_query_response';

    /**
     * @var AopTradeQueryRequest
     */
    protected $request;


    public function isPaid()
    {
        if ($this->getTradeStatus() == 'TRADE_SUCCESS') {
            return true;
        } elseif ($this->getTradeStatus() == 'TRADE_FINISHED') {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @return mixed
     */
    public function getTradeStatus()
    {
        return $this->getAlipayResponse('trade_status');
    }


    public function isWaitPay()
    {
        return $this->getTradeStatus() == 'WAIT_BUYER_PAY';
    }


    public function isClosed()
    {
        return $this->getTradeStatus() == 'TRADE_CLOSED';
    }
}
