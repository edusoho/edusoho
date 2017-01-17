<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TaskDaoImpl extends GeneralDaoImpl implements TaskDao
{
    protected $table = 'course_task';

    public function deleteByCategoryId($categoryId)
    {
        return $this->db()->delete($this->table(), array('categoryId' => $categoryId));
    }

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? ORDER  BY seq";
        return $this->db()->fetchAll($sql, array($courseId)) ?: array();
    }

    public function findByCourseIds($courseIds)
    {
        return $this->findInField('courseId', $courseIds);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getMaxSeqByCourseId($courseId)
    {
        $sql = "SELECT MAX(seq) FROM {$this->table()} WHERE courseId = ? ";
        return $this->db()->fetchColumn($sql, array($courseId)) ?: 0;
    }

    public function getMinSeqByCourseId($courseId)
    {
        $sql = "SELECT MIN(seq) FROM {$this->table()} WHERE courseId = ? ";
        return $this->db()->fetchColumn($sql, array($courseId)) ?: 0;
    }

    public function getNextTaskByCourseIdAndSeq($courseId, $seq)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE seq > ? and courseId = ?  ORDER BY seq ASC LIMIT 1 ";
        return $this->db()->fetchAssoc($sql, array($seq, $courseId));
    }

    public function getPreTaskByCourseIdAndSeq($courseId, $seq)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE seq < ? and courseId = ?  ORDER BY seq DESC LIMIT 1 ";
        return $this->db()->fetchAssoc($sql, array($seq, $courseId));
    }

    public function findByChapterId($chapterId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE categoryId = ? ";
        return $this->db()->fetchAll($sql, array($chapterId)) ?: array();
    }

    public function getByChapterIdAndMode($chapterId, $mode)
    {
        $sql = "SELECT * FROM {$this->table()}  WHERE `categoryId`= ? AND `mode` = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($chapterId, $mode));
    }

    public function getByCourseIdAndSeq($courseId, $sql)
    {
        return $this->getByFields(array('courseId' => $courseId, 'seq' => $sql));
    }

    public function getTaskByCourseIdAndActivityId($courseId, $activityId)
    {
        return $this->getByFields(array('courseId' => $courseId, 'activityId' => $activityId));
    }

    public function findByCourseIdAndIsFree($courseId, $isFree)
    {
        return $this->findByFields(array('courseId' => $courseId, 'isFree' => $isFree));
    }

    public function getLearnTimeByCourseSetId($courseSetId)
    {
        $sql = "select sum(`time`) as learnTime from `course_task_result` where `courseTaskId` in (SELECT id FROM {$this->table()}  WHERE `fromCourseSetId`= ?)";
        return $this->db()->fetchColumn($sql, array($courseSetId)); 
    }

    public function declares()
    {
        return array(
            'orderbys'   => array('seq'),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'courseId = :courseId',
                'fromCourseSetId = :fromCourseSetId',
                'status =:status',
                'type = :type',
                'type IN ( :types )',
                'seq >= :seq_GE',
                'seq > :seq_GT',
                'seq < :seq_LT'
            )
        );
    }
}
