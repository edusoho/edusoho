<?php

namespace Codeages\Biz\Pay\Status;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

abstract class AbstractStatus
{
    protected $PayTrade;
    protected $biz;

    public function setPayTrade($PayTrade)
    {
        $this->PayTrade = $PayTrade;
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
        $status->setPayTrade($this->PayTrade);
        return $status;
    }

    protected function getPayTradeDao()
    {
        return $this->biz->dao('Pay:PayTradeDao');
    }
}