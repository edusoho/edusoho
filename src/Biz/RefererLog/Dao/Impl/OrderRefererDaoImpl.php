<?php

namespace Biz\RefererLog\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RefererLog\Dao\OrderRefererDao;

class OrderRefererDaoImpl extends GeneralDaoImpl implements OrderRefererDao
{
    protected $table = 'order_referer';

    public function declares()
    {
        return array(
            'timestamps' => array(),
            'serializes' => array('data' => 'php'),
            'orderbys' => array('createdTime', 'recommendedSeq', 'studentNum', 'hitNum'),
        );
    }

    public function getByUv($uv)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE uv = ?  AND expiredTime >= ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($uv, time())) ?: null;
    }

    public function getLikeByOrderId($orderId)
    {
        $likeOrderIds = '%|'.$orderId.'|%';
        $sql = "SELECT * FROM {$this->table()} WHERE orderIds like ?  LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($likeOrderIds)) ?: null;
    }
}
