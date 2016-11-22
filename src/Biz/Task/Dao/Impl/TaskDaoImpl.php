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

    public function getMaxTaskSeqByCourseId($courseId)
    {
        $sql = "SELECT max(seq) FROM coruse_task WHERE `courseId` = ? LIMIT 1";
        return $this->db()->fetchColumn($sql, array($courseId));
    }

    public function getByCourseIdAndSeq($courseId, $seq)
    {
        $sql = "SELECT * FROM `course_task` WHERE `courseId`= ? AND `seq` = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($courseId, $seq));
    }


    public function declares()
    {
        return array();
    }
}
