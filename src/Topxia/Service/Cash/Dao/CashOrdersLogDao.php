<?php

namespace Topxia\Service\Cash\Dao;

interface CashOrdersLogDao
{
    public function addLog($fields);

    public function getLogsByOrderId($orderId);

    public function getOrderLog($id);

}