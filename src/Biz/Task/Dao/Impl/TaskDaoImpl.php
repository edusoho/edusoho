<?php

namespace Biz\Task\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Task\Dao\TaskDao;

class TaskDaoImpl extends GeneralDaoImpl implements TaskDao
{
    protected $table = 'course_task';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? ";
        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function getMaxSeqByCourseId($courseId)
    {
        $sql = "SELECT max(seq) FROM {$this->table()} WHERE courseId = ? ";
        return $this->db()->fetchColumn($sql, array($courseId)) ?: 0;
    }

    public function declares()
    {
        return array();
    }
}
