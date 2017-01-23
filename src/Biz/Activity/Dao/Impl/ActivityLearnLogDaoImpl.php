<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ActivityLearnLogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ActivityLearnLogDaoImpl extends GeneralDaoImpl implements ActivityLearnLogDao
{
    protected $table = 'activity_learn_log';

    public function sumLearnedTimeByActivityId($activityId)
    {
        $sql = "SELECT sum(learnedTime) FROM {$this->table()} WHERE activityId = ?";
        return $this->db()->fetchColumn($sql, array($activityId)) ?: 0;
    }

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
        return $this->db()->fetchAll($sql, array($activityId, $userId, $event)) ?: array();
    }

    public function countLearnedDaysByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT count(distinct(from_unixtime(createdTime, '%Y-%m-%d'))) FROM {$this->table()} WHERE userId = ? AND activityId IN (SELECT id FROM activity WHERE fromCourseId = ?)";
        return $this->db()->fetchColumn($sql, array($userId, $courseId)) ?: 0;
    }

    public function sumLearnTime($conditions)
    {
        if (!empty($conditions['taskId'])) {
            $sql        = "SELECT activityId FROM course_task where id = ? limit 1";
            $activityId = $this->db()->fetchColumn($sql, array($conditions['taskId']));

            if (empty($activityId) || $activityId <= 0) {
                return 0;
            }
            unset($conditions['taskId']);
            $conditions['activityId'] = $activityId;
        }
        $builder = $this->_createQueryBuilder($conditions)
            ->select('sum(learnedTime)');

        return $builder->execute()->fetchColumn();
    }

    public function deleteByActivityId($activityId)
    {
        return $this->db()->delete($this->table(), array('activityId' => $activityId));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'data' => 'json'
            ),
            'conditions' => array(
                'activityId = :activityId',
                'userId = :userId'
            )
        );
    }
}
