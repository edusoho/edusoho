<?php

namespace Codeages\Biz\Order\Service;

interface OrderRefundService
{
    public function searchRefunds($conditions, $orderby, $start, $limit);

    public function countRefunds($conditions);

    public function getOrderRefundById($id);

    public function findOrderItemRefundsByOrderRefundId($orderRefundId);

    public function findRefundsByOrderIds($orderIds);
}