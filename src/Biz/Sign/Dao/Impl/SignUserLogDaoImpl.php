<?php

namespace Biz\Sign\Dao\Impl;

use Biz\Sign\Dao\SignUserLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SignUserLogDaoImpl extends GeneralDaoImpl implements SignUserLogDao
{
    protected $table = 'sign_user_log';

    public function declares()
    {
        return [
            'orderbys' => ['createdTime', 'id'],
            'conditions' => [
                'userId = :userId',
                'targetType = :targetType',
                'targetId = :targetId',
                'userId IN (:userIds)',
                'createdTime >= createdTime_GT',
            ],
        ];
    }

    public function findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $endTime)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? AND targetType = ? AND targetId = ? AND createdTime > ? AND createdTime < ? ORDER BY createdTime ASC;";

        return $this->db()->fetchAll($sql, [$userId, $targetType, $targetId, $startTime, $endTime]) ?: [];
    }
}
