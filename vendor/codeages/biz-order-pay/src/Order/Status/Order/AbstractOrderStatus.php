<?php

namespace Codeages\Biz\Order\Status\Order;

use Codeages\Biz\Order\Exception\OrderStatusException;

abstract class AbstractOrderStatus implements OrderStatus
{
    protected $biz;
    protected $order;

    abstract public function getName();

    abstract public function process($data);

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function start($order, $orderItems)
    {
        throw new OrderStatusException('can not start order.');
    }

    public function paying($data = array())
    {
        throw new OrderStatusException("can not change order #{$this->order['id']} status to paying.");
    }

    public function paid($data = array())
    {
        throw new OrderStatusException("can not change order #{$this->order['id']} status to paid.");
    }

    public function closed($data = array())
    {
        throw new OrderStatusException("can not change order #{$this->order['id']} status to closed.");
    }

    public function success($data = array())
    {
        throw new OrderStatusException("can not change order #{$this->order['id']} status to success.");
    }

    public function fail($data = array())
    {
        throw new OrderStatusException("can not change order #{$this->order['id']} status to fail.");
    }

    public function finished($data = array())
    {
        throw new OrderStatusException("can not change order #{$this->order['id']} status to finished.");
    }

    protected function getOrderStatus($name)
    {
        $orderStatus = $this->biz['order_status.'.$name];
        $orderStatus->setOrder($this->order);
        return $orderStatus;
    }

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