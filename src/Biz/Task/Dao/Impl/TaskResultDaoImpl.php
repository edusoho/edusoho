<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TaskResultDaoImpl extends GeneralDaoImpl implements TaskResultDao
{
    protected $table = 'course_task_result';

    public function findByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? ";
        return $this->db()->fetchAll($sql, array($courseId, $userId)) ?: array();
    }

    public function getByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getByFields(array(
            'courseTaskId' => $taskId,
            'userId'       => $userId
        ));
    }

    public function findByTaskIdsAndUserId($taskIds, $userId)
    {
        $marks = str_repeat('?,', count($taskIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE courseTaskId IN ({$marks}) and userId = ? ;";

        $parameters = array_merge($taskIds, array($userId));
        return $this->db()->fetchAll($sql, $parameters) ?: array();
    }

    public function findByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ? and userId = ? ";
        return $this->db()->fetchAll($sql, array($activityId, $userId)) ?: array();
    }

    public function deleteByTaskIdAndUserId($taskId, $userId)
    {
        return $this->db()->delete($this->table(), array('courseTaskId' => $taskId, 'userId' => $userId));
    }

    public function countLearnNumByTaskId($taskId)
    {
        $sql = "SELECT count(id) FROM {$this->table()} WHERE courseTaskId = ? ";
        return $this->db()->fetchColumn($sql, array($taskId));
    }

    public function findFinishedTasksByCourseIdGroupByUserId($courseId)
    {
        $sql = "SELECT count(courseTaskId) as taskCount, userId FROM {$this->table()} WHERE courseId = ? and status='finish' group by userId";
        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function findFinishedTimeByCourseIdGroupByUserId($courseId)
    {
        //已发布task总数
        $sql            = "SELECT count(1) FROM course_task WHERE courseId = ? AND status='published'";
        $totalTaskCount = $this->db()->fetchColumn($sql, array($courseId));

        if ($totalTaskCount <= 0) {
            return array();
        }

        $sql = "SELECT max(finishedTime) AS finishedTime, count(courseTaskId) AS taskCount, userId FROM {$this->table()} WHERE courseId = ? and status='finish' group by userId HAVING taskCount >= ?";

        return $this->db()->fetchAll($sql, array($courseId, $totalTaskCount)) ?: array();
    }

    public function declares()
    {
        return array(
            'orderbys'   => array('createdTime', 'updatedTime'),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'status =:status',
                'userId =:userId',
                'courseId =:courseId',
                'activityId =:activityId',
                'courseTaskId = :courseTaskId',
                'createdTime >= :createdTime_GE',
                'createdTime <= :createdTime_LE'
            )
        );
    }
}
