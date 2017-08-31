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

    public function getPayStatus($name)
    {
        $status = $this->biz['payment_trade_status.'.$name];
        $status->setPaymentTrade($this->paymentTrade);
        return $status;
    }

    abstract public function getPriorStatus();

    protected function getPaymentTradeDao()
    {
        return $this->biz->dao('Pay:PaymentTradeDao');
    }
}