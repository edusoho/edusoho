<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ActivityLearnLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ActivityLearnLogDaoImpl extends GeneralDaoImpl implements ActivityLearnLogDao
{
    protected $table = 'activity_learn_log';

    public function sumLearnedTimeByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT sum(learnedTime) FROM {$this->table()} WHERE activityId = ? and userId = ? ";
        return $this->db()->fetchColumn($sql, array($activityId, $userId)) ?: 0;
    }

    public function sumLearnedTimeByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT sum(learnedTime) FROM {$this->table()} WHERE userId = ? AND activityId IN (SELECT id FROM activity WHERE fromCourseId = ?)";
        return $this->db()->fetchColumn($sql, array($userId, $courseId)) ?: 0;
    }

    public function findByActivityIdAndUserIdAndEvent($activityId, $userId, $event)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ? and userId = ? and event = ?";
        return $this->db()->fetchColumn($sql, array($activityId, $userId, $event)) ?: 0;
    }

    public function countLearnedDaysByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT count(distinct(from_unixtime(createdTime, '%Y-%m-%d'))) FROM {$this->table()} WHERE userId = ? AND activityId IN (SELECT id FROM activity WHERE fromCourseId = ?)";
        return $this->db()->fetchColumn($sql, array($userId, $courseId)) ?: 0;
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'data' => 'json'
            )
        );
    }

}
