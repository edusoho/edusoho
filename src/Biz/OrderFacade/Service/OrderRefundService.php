<?php

namespace Biz\OrderFacade\Service;

interface OrderRefundService
{
    public function searchRefunds($conditions, $orderBy, $start, $limit);

    public function countRefunds($conditions);

    public function getOrderRefundById($id);

    public function applyOrderRefund($orderId, $fileds);

    public function cancelRefund($orderId);

    public function refuseRefund($orderId, $data);

    public function adoptRefund($orderId, $data);
}
