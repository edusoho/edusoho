<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeRefundQueryRequest;

class AopTradeRefundQueryResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_fastpay_refund_query_response';

    /**
     * @var AopTradeRefundQueryRequest
     */
    protected $request;
}
