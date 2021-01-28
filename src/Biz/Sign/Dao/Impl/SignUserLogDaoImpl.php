<?php

namespace Biz\Sign\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Sign\Dao\SignUserLogDao;

class SignUserLogDaoImpl extends GeneralDaoImpl implements SignUserLogDao
{
    protected $table = 'sign_user_log';

    public function declares()
    {
    }

    public function findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $endTime)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? AND targetType = ? AND targetId = ? AND createdTime > ? AND createdTime < ? ORDER BY createdTime ASC;";

        return $this->db()->fetchAll($sql, array($userId, $targetType, $targetId, $startTime, $endTime)) ?: array();
    }
}
