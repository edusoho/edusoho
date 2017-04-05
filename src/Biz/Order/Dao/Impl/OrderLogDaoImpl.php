<?php

namespace Biz\Order\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Order\Dao\OrderLogDao;

class OrderLogDaoImpl extends GeneralDaoImpl implements OrderLogDao
{
    protected $table = 'order_log';

    public function declares()
    {
        return array();
    }

    public function findByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderId = ?";

        return $this->db()->fetchAll($sql, array($orderId));
    }

    public function findByOrderIds(array $orderIds)
    {
        return $this->findInField('orderId', $orderIds);
    }
}
