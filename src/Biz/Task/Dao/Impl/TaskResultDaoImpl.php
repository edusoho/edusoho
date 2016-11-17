<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TaskResultDaoImpl extends GeneralDaoImpl implements TaskResultDao
{
    protected $table = 'course_task_result';

    public function findByCourseId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? ";
        return $this->db()->fetchAll($sql, array($courseId, $userId)) ?: array();
    }

    public function findByTaskId($courseTaskId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseTaskId = ? and userId = ? ";
        return $this->db()->fetchAll($sql, array($courseTaskId, $userId)) ?: array();
    }

    public function getByTaskIdAndActivityId($taskId, $activityId)
    {
        return $this->getByFields(array(
            'courseTaskId' => $taskId,
            'activityId'   => $activityId
        ));
    }

    public function getByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getByFields(array(
            'courseTaskId' => $taskId,
            'userId'       => $userId
        ));
    }


    public function declares()
    {
        return array();
    }
}
