<?php

namespace Biz\Cash\Dao\Impl;

use Biz\Cash\Dao\CashOrdersLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CashOrdersLogDaoImpl extends GeneralDaoImpl implements CashOrdersLogDao
{
    protected $table = 'cash_orders_log';

    public function findByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderId = ? ";

        return $this->db()->fetchAll($sql, array($orderId)) ?: array();
    }

    public function declares()
    {
        return array();
    }
}
