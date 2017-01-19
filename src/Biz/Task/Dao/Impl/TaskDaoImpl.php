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

    public function findByActivityIds($activityIds)
    {
        return $this->findInField('activityId', $activityIds);
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
        $sql = "SELECT * FROM {$this->table()} WHERE categoryId = ? ORDER BY seq ASC ";
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

    /**
     * 统计当前时间以后每天的直播次数
     *
     * @param $courseSetIds
     * @param $limit
     *
     * @return array <string, int|string>
     */
    public function findFutureLiveDatesByCourseSetIdsGroupByDate($courseSetIds, $limit)
    {
        if (empty($courseSetIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseSetIds) - 1).'?';

        $time = time();

        $sql = "SELECT count( id) as count, from_unixtime(startTime,'%Y-%m-%d') as date FROM `{$this->table()}` WHERE  `type`= 'live' AND status='published' AND fromCourseSetId IN ({$marks}) AND startTime >= {$time} group by date order by date ASC limit 0, {$limit}";
        return $this->db()->fetchAll($sql, $courseSetIds);
    }

    /**
     * 返回过去直播过的教学计划ID
     *
     * @return array<int>
     */
    public function findPastLivedCourseSetIds()
    {
        $time = time();
        $sql  = "SELECT fromCourseSetId, max(startTime) as startTime
                 FROM {$this->table()} 
                 WHERE endTime < {$time} AND status='published' AND type = 'live' 
                 GROUP BY fromCourseSetId 
                 ORDER BY startTime DESC
                 ";
        return $this->db()->fetchAll($sql);
    }

    public function getTaskByCourseIdAndActivityId($courseId, $activityId)
    {
        return $this->getByFields(array('courseId' => $courseId, 'activityId' => $activityId));
    }

    public function findByCourseIdAndIsFree($courseId, $isFree)
    {
        return $this->findByFields(array('courseId' => $courseId, 'isFree' => $isFree));
    }

    public function sumCourseSetLearnedTimeByCourseSetId($courseSetId)
    {
        $sql = "select sum(`time`) from `course_task_result` where `courseTaskId` in (SELECT id FROM {$this->table()}  WHERE `fromCourseSetId`= ?)";
        return $this->db()->fetchColumn($sql, array($courseSetId)); 
    }

    public function declares()
    {
        return array(
            'orderbys'   => array('seq', 'startTime'),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'courseId = :courseId',
                'courseId IN ( :courseIds )',
                'fromCourseSetId = :fromCourseSetId',
                'fromCourseSetId IN (:fromCourseSetIds)',
                'status =:status',
                'type = :type',
                'isFree =:isFree',
                'type IN ( :types )',
                'seq >= :seq_GE',
                'seq > :seq_GT',
                'seq < :seq_LT',
                'startTime >= :startTime_GE',
                'startTime > :startTime_GT',
                'endTime > :endTime_GT',
                'endTime < :endTime_LT'
            )
        );
    }
}
