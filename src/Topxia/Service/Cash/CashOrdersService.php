<?php
namespace Topxia\Service\Cash;

interface CashOrdersService
{
    public function addOrder($order);

    public function getOrder($id);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function searchOrdersCount($conditions);

    public function getLogsByOrderId($orderId);

    public function analysisAmount($conditions);
}