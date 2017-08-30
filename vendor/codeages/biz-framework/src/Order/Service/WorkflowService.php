<?php

namespace Codeages\Biz\Framework\Order\Service;

interface WorkflowService
{
    public function start($order, $orderItems);

    public function close($orderId, $data = array());

    public function paying($orderId, $data = array());

    public function paid($data);

    public function finish($orderId, $data = array());

    public function fail($orderId, $data = array());

    public function applyOrderItemRefund($orderItemId, $data);

    public function applyOrderRefund($orderId, $data);

    public function applyOrderItemsRefund($orderId, $orderItemIds, $data);

    public function adoptRefund($refundId, $data = array());

    public function refuseRefund($refundId, $data = array());

    public function setRefunded($refundId, $data = array());

    public function cancelRefund($refundId);

    public function closeOrders();
}