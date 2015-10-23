<?php

namespace Topxia\Service\Cash\Dao;

interface CashOrdersDao
{
    public function getOrder($id);

    public function addOrder($fields);

    public function getOrderBySn($sn,$lock=false);

    public function updateOrder($id, $fields);

    public function searchOrders($conditions, $orderBy, $start, $limit);

    public function searchOrdersCount($conditions);

    public function analysisAmount($conditions);

    public function closeOrders($time);
}