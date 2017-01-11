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

    public function countUsersByTaskIdAndLearnStatus($taskId, $status)
    {
        $sql = "SELECT count(userId) FROM {$this->table()} WHERE courseTaskId = ? ";

        if ('all' == $status) {
            return $this->db()->fetchColumn($sql, array($taskId));
        }
        $sql .= " AND status = ?";
        return $this->db()->fetchColumn($sql, array($taskId, $status));
    }

    public function countLearnNumByTaskId($taskId)
    {
        $sql = "SELECT count(id) FROM {$this->table()} WHERE courseTaskId = ? ";
        return $this->db()->fetchColumn($sql, array($taskId));
    }

    public function countUserLearnedByCourseId($courseId, $userId)
    {
        $sql = "SELECT count(distinct(id)) FROM {$this->table()} WHERE courseId = ? and userId = ? ";
        return $this->db()->fetchColumn($sql, array($courseId, $userId));
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
                'courseTaskId =: courseTaskId'
            )
        );
    }
}
