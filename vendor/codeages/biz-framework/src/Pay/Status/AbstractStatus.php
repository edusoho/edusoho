<?php

namespace Codeages\Biz\Framework\Pay\Status;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

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

    abstract public function getName();

    public function process($data = array())
    {
        throw new AccessDeniedException('can not change status to '.$this->getName());
    }

    public function getPayStatus($name)
    {
        $status = $this->biz['payment_trade_status.'.$name];
        $status->setPaymentTrade($this->paymentTrade);
        return $status;
    }

    protected function getPaymentTradeDao()
    {
        return $this->biz->dao('Pay:PaymentTradeDao');
    }
}