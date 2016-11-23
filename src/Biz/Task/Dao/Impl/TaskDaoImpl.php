<?php

namespace Biz\Task\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Task\Dao\TaskDao;

class TaskDaoImpl extends GeneralDaoImpl implements TaskDao
{
    protected $table = 'course_task';

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? ORDER  BY seq";
        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function getByCourseIdAndSeq($courseId, $seq)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE `courseId`= ? AND `seq` = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($courseId, $seq));
    }


    public function getMaxSeqByCourseId($courseId)
    {
        $sql = "SELECT max(seq) FROM {$this->table()} WHERE courseId = ? ";
        return $this->db()->fetchColumn($sql, array($courseId)) ?: 0;
    }

    public function findTasksByChapterId($chapterId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseChapterId = ? ";
        return $this->db()->fetchAll($sql, array($chapterId)) ?: array();
    }

    public function decreaseSeq($seq)
    {
        $sql = "UPDATE {$this->table()} SET seq = seq-1 WHERE seq >?";
        return $this->db()->executeUpdate($sql, array($seq));
    }

    public function declares()
    {
        return array();
    }
}
