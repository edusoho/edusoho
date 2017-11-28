<?php

namespace Codeages\Biz\Order\Status\Refund;

abstract class AbstractRefundStatus extends \Codeages\Biz\Order\Status\AbstractStatus
{
    protected $orderRefund;

    public function setOrderRefund($orderRefund)
    {
        $this->orderRefund = $orderRefund;
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