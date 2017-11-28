<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeRefundRequest;
class AopTradeCloseResponse extends \Omnipay\Alipay\Responses\AbstractAopResponse
{
    protected $key = 'alipay_trade_close_response';
    /**
     * @var AopTradeRefundRequest
     */
    protected $request;
}