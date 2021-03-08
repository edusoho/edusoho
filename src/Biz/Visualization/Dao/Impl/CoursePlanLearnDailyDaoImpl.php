<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CoursePlanLearnDailyDaoImpl extends AdvancedDaoImpl implements CoursePlanLearnDailyDao
{
    protected $table = 'course_plan_learn_daily';

    public function sumLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds)
    {
        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT sum(`sumTime`) AS learnedTime, `userId` FROM `{$this->table}` WHERE `courseId`= ? AND userId IN ($marks) GROUP BY `userId`;";

        return $this->db()->fetchAll($sql, array_merge([$courseId], $userIds));
    }

    public function sumPureLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds)
    {
        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT sum(`pureTime`) AS learnedTime, `userId` FROM `{$this->table}` WHERE `courseId`= ? AND userId IN ($marks) GROUP BY `userId`;";

        return $this->db()->fetchAll($sql, array_merge([$courseId], $userIds));
    }

    public function sumLearnedTimeByCourseId($courseId)
    {
        $sql = "SELECT sum(`sumTime`) AS learnedTime FROM `{$this->table}` WHERE `courseId`= ?;";

        return $this->db()->fetchAssoc($sql, [$courseId])['learnedTime'];
    }

    public function sumLearnedTimeByCourseIds($courseIds)
    {
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT sum(`sumTime`) AS learnedTime FROM `{$this->table}` WHERE `courseId` IN ({$marks});";
        $res = $this->db()->fetchAssoc($sql, $courseIds);

        return empty($res['learnedTime']) ? 0 : $res['learnedTime'];
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
            ],
            'orderbys' => ['id', 'createdTime', 'userId'],
        ];
    }
}
