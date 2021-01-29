<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityLearnDailyDaoImpl extends AdvancedDaoImpl implements ActivityLearnDailyDao
{
    protected $table = 'activity_learn_daily';

    public function findByCourseSetIds($courseSetIds)
    {
        return $this->findInField('courseSetId', $courseSetIds);
    }

    public function sumLearnedTimeGroupByTaskIds(array $taskIds)
    {
        $marks = str_repeat('?,', count($taskIds) - 1).'?';

        $sql = "SELECT sum(`sumTime`) AS learnedTime, `taskId` FROM `{$this->table}` WHERE `taskId` IN ($marks) GROUP BY `taskId`;";

        return $this->db()->fetchAll($sql, $taskIds);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
                'dayTime >= :dayTime_GE',
                'dayTime <= :dayTime_LE',
                'userId IN (:userIds)',
                'userId = :userId',
                'dayTime = :dayTime',
                'taskId in (:taskIds)',
                'mediaType = :mediaType',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
