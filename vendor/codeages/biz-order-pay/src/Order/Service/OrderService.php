<?php

namespace Codeages\Biz\Order\Service;

interface OrderService
{
    public function findOrderItemsByOrderId($orderId);

    public function findOrderItemsByOrderIds($orderIds);

    public function findOrderItemDeductsByItemId($itemId);

    public function getOrderItem($id);

    public function getOrder($id);

    public function getOrderBySn($sn, $lock = false);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function countOrders($conditions);

    public function countGroupByDate($conditions, $sort, $dateColumn = 'pay_time');

    public function sumGroupByDate($column, $conditions, $sort, $dateColumn = 'pay_time');

    public function searchOrderItems($conditions, $orderBy, $start, $limit);

    public function countOrderItems($conditions);

    public function findOrdersByIds(array $ids);

    public function findOrdersBySns(array $orderSns);

    public function findOrdersByInvoiceSn($invoiceSn);

    public function findOrderItemDeductsByOrderId($orderId);

    public function findOrderLogsByOrderId($orderId);

    public function countOrderLogs($conditions);

    public function searchOrderLogs($conditions, $orderBy, $start, $limit);

    public function getOrderItemByOrderIdAndTargetIdAndTargetType($orderId, $targetId, $targetType);

    public function sumOrderItemPayAmount($conditions);

    public function sumPaidAmount($conditions);

    public function addOrderItemDeduct($deduct);

    public function updateOrderItemDeduct($deductId, $updateFields);

    public function updateOrderInvoiceSnByOrderId($orderId, $invoiceSn);
}
