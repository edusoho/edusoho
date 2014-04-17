<?php

namespace Topxia\Service\Order\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Order\Dao\OrderLogDao;

class OrderLogDaoImpl extends BaseDao implements OrderLogDao
{
    protected $table = 'order_log';

    public function getLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addLog($log)
    {
        $affected = $this->getConnection()->insert($this->table, $log);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert log error.');
        }
        return $this->getLog($this->getConnection()->lastInsertId());
    }

    public function findLogsByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderId = ?";
        return $this->getConnection()->fetchAll($sql, array($orderId));
    }

}