<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderService
{
    public function findOrderItemsByOrderId($orderId);

    public function findOrderItemDeductsByItemId($itemId);

    public function createOrder($order, $orderItems);

    public function setOrderClosed($id, $data = array());

    public function setOrderPaid($data);

    public function setOrderPaying($id, $data = array());

    public function setOrderSuccess($id, $data = array());

    public function setOrderFail($id, $data = array());

    public function setOrderRefunding($id, $data = array());

    public function setOrderRefunded($id, $data = array());

    public function closeOrders();

    public function getOrder($id);

    public function getOrderBySn($sn, $lock = false);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function countOrders($conditions);

    public function searchOrderItems($conditions, $orderBy, $start, $limit);

    public function countOrderItems($conditions);

    public function findOrdersByIds(array $ids);

    public function findOrderItemDeductsByOrderId($orderId);
}