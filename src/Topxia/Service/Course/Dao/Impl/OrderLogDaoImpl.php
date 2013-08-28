<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\OrderLogDao;

class OrderLogDaoImpl extends BaseDao implements OrderLogDao
{
    protected $table = 'course_order_log';

    public function getLog($id)
    {
    	return $this->fetch($id);
    }

    public function addLog($log)
    {
        $id = $this->insert($log);
    	return $this->getLog($id);
    }

    public function findLogsByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE orderId = ?";
        return $this->getConnection()->fetchAll($sql, array($orderId));
    }

}