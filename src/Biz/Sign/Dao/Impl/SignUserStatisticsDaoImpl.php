<?php

namespace Biz\Sign\Dao\Impl;

use Biz\Sign\Dao\SignUserStatisticsDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class SignUserStatisticsDaoImpl extends AdvancedDaoImpl implements SignUserStatisticsDao
{
    protected $table = 'sign_user_statistics';

    public function declares()
    {
        return [
            'orderbys' => ['id'],
            'conditions' => [
                'userId = :userId',
                'targetType = :targetType',
                'targetId = :targetId',
                'userId IN (:userIds)',
                'id IN (:ids)',
                'lastSignTime >= :lastSignTime_GT',
                'lastSignTime <= :lastSignTime_LT',
            ],
        ];
    }

    public function getStatisticsByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ?  AND targetType = ? AND targetId = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$userId, $targetType, $targetId]) ?: null;
    }
}
