<?php

namespace Codeages\Biz\Framework\Order\Service;

interface OrderRefundService
{
    public function applyOrderItemRefund($id, $data);

    public function applyOrderRefund($orderId, $data);

    public function applyOrderItemsRefund($orderId, $orderItemIds, $data);

    public function adoptRefund($id, $data = array());

    public function refuseRefund($id, $data = array());

    public function setRefunded($id, $data = array());

    public function cancelRefund($id);

}