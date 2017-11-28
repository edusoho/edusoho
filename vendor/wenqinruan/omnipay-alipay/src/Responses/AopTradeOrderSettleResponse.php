<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradeOrderSettleRequest;
class AopTradeOrderSettleResponse extends \Omnipay\Alipay\Responses\AbstractAopResponse
{
    protected $key = 'alipay_trade_order_settle_response';
    /**
     * @var AopTradeOrderSettleRequest
     */
    protected $request;
}