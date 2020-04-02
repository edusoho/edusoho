<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeRefundRequest;

class AopTradeCancelResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_cancel_response';

    /**
     * @var AopTradeRefundRequest
     */
    protected $request;
}
