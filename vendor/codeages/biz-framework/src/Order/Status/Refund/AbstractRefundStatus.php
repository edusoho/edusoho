<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

abstract class AbstractRefundStatus extends \Codeages\Biz\Framework\Order\Status\AbstractStatus
{
    protected $orderRefund;

    public function setOrderRefund($orderRefund)
    {
        $this->orderRefund = $orderRefund;
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }
}