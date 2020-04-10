<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeRefundRequest;

class AopTradeCloseResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_close_response';

    /**
     * @var AopTradeRefundRequest
     */
    protected $request;
}
