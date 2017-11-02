<?php

namespace Codeages\Biz\Order\Status\Order;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

abstract class AbstractOrderStatus extends \Codeages\Biz\Order\Status\AbstractStatus
{
    protected $order;

    protected function changeStatus($name)
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => $name,
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => $name,
            ));
        }

        $deducts = $this->getOrderItemDeductDao()->findByOrderId($this->order['id']);
        foreach ($deducts as $key => $deduct) {
            $deducts[$key] = $this->getOrderItemDeductDao()->update($deduct['id'], array(
                'status' => $name
            ));
        }
        return $order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrderStatus($name)
    {
        $orderStatus = $this->biz['order_status.'.$name];
        $orderStatus->setOrder($this->order);
        return $orderStatus;
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderItemDeductDao()
    {
        return $this->biz->dao('Order:OrderItemDeductDao');
    }
}
