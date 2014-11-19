<?php

namespace Topxia\Service\Cash\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Cash\Dao\CashOrdersLogDao;

class CashOrdersLogDaoImpl extends BaseDao implements CashOrdersLogDao
{   
    protected $table = 'cash_orders_log';

    public function getLogsByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderId = ? ";
        return $this->getConnection()->fetchAll($sql, array($orderId)) ? : array();
    }

    public function addLog($fields)
    {
        $order = $this->getConnection()->insert($this->table, $fields);
        if ($order <= 0) {
            throw $this->createDaoException('Insert cash_orders account error.');
        }
        return $this->getOrderLog($this->getConnection()->lastInsertId());
    }

    public function getOrderLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

}