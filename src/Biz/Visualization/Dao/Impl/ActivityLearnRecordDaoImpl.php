<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityLearnRecordDaoImpl extends AdvancedDaoImpl implements ActivityLearnRecordDao
{
    protected $table = 'activity_learn_record';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'serializes' => [
                'data' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'startTime >= :startTime_GE',
                'endTime < :endTime_LT',
                'courseId in (:courseIds)',
                'userId in (:userIds)',
                'courseId = :courseId',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }

    public function getUserLastLearnRecord($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE  userId = ? ORDER BY endTime DESC LIMIT 1;";

        return $this->db()->fetchAssoc($sql, [$userId]);
    }

    public function getUserLastLearnRecordBySign($userId, $sign)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND flowSign = ? ORDER BY endTime DESC LIMIT 1;";

        return $this->db()->fetchAssoc($sql, [$userId, $sign]);
    }
}
