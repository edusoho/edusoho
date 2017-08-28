<?php

namespace Codeages\Biz\Framework\Pay\Status;

abstract class AbstractStatus
{
    protected $paymentTrade;
    protected $biz;

    public function setPaymentTrade($paymentTrade)
    {
        $this->paymentTrade = $paymentTrade;
    }

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getPriorStatus();

    protected function getPaymentTradeDao()
    {
        return $this->biz->dao('Pay:PaymentTradeDao');
    }
}