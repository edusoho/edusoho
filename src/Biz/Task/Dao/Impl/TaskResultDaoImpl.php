<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TaskResultDaoImpl extends GeneralDaoImpl implements TaskResultDao
{
    protected $table = 'course_task_result';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? ";
        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function findByTaskId($courseTaskId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseTaskId = ? ";
        return $this->db()->fetchAll($sql, array($courseTaskId)) ?: array();
    }

    public function save($taskResult)
    {
        //TODO create or update
    }

    public function declares()
    {
        return array();
    }
}
