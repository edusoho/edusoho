<?php

namespace Codeages\Biz\Order\Status\Refund;

use Codeages\Biz\Order\Exception\OrderRefundStatusException;

abstract class AbstractRefundStatus implements RefundStatus
{
    protected $orderRefund;
    protected $biz;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getName();

    public function setOrderRefund($orderRefund)
    {
        $this->orderRefund = $orderRefund;
    }

    public function refunding($data)
    {
        throw new OrderRefundStatusException("can not change order_refund #{$this->orderRefund['id']} to refunding.");
    }

    public function refused($data)
    {
        throw new OrderRefundStatusException("can not change order_refund #{$this->orderRefund['id']} to refused.");
    }

    public function refunded($data)
    {
        throw new OrderRefundStatusException("can not change order_refund #{$this->orderRefund['id']} to refunded.");
    }

    public function cancel()
    {
        throw new OrderRefundStatusException("can not change order_refund #{$this->orderRefund['id']} to cancel.");
    }

    protected function changeStatus($name)
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'status' => $name
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => $name
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => $name
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;
        return $orderRefund;
    }

    public function getOrderRefundStatus($name)
    {
        $status = $this->biz['order_refund_status.'.$name];
        $status->setOrderRefund($this->orderRefund);
        return $status;
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