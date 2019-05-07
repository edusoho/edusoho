<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopTradePreCreateRequest;
class AopTradePreCreateResponse extends \Omnipay\Alipay\Responses\AbstractAopResponse
{
    protected $key = 'alipay_trade_precreate_response';
    /**
     * @var AopTradePreCreateRequest
     */
    protected $request;
    public function getQrCode()
    {
        return $this->getAlipayResponse('qr_code');
    }
}