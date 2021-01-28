<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class TaskDaoImpl extends AdvancedDaoImpl implements TaskDao
{
    protected $table = 'course_task';

    public function deleteByCategoryId($categoryId)
    {
        return $this->db()->delete($this->table(), ['categoryId' => $categoryId]);
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), ['courseId' => $courseId]);
    }

    public function getUserCurrentPublishedLiveTaskByTimeRange($userId, $startTime, $endBeforeTimeRange)
    {
        $currentTime = time();
        $sql = "SELECT * FROM {$this->table} WHERE type='live' and status='published' and courseId IN (SELECT courseId FROM `course_member` WHERE role = 'student' AND userId = ?) 
                and (startTime > {$currentTime} and startTime < ? 
                or  
                startTime < {$currentTime} and endTime - ? > {$currentTime}) 
                ORDER BY startTime ASC LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$userId, $startTime, $endBeforeTimeRange]) ?: [];
    }

    public function findByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? ORDER  BY seq";

        return $this->db()->fetchAll($sql, [$courseId]) ?: [];
    }

    public function findByCourseIds($courseIds)
    {
        return $this->findInField('courseId', $courseIds);
    }

    public function findByActivityIds($activityIds)
    {
        return $this->findInField('activityId', $activityIds);
    }

    public function findByCourseSetId($courseSetId)
    {
        return $this->findByFields([
            'fromCourseSetId' => $courseSetId,
        ]);
    }

    public function findByCourseSetIds($courseSetIds)
    {
        return $this->findInField('fromCourseSetId', $courseSetIds);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByCourseIdAndCategoryId($courseId, $categoryId)
    {
        return $this->findByFields(['courseId' => $courseId, 'categoryId' => $categoryId]);
    }

    public function getMaxSeqByCourseId($courseId)
    {
        $sql = "SELECT MAX(seq) FROM {$this->table()} WHERE courseId = ? ";

        return $this->db()->fetchColumn($sql, [$courseId]) ?: 0;
    }

    public function getNumberSeqByCourseId($courseId)
    {
        $sql = "SELECT MAX(number) FROM {$this->table()} WHERE courseId = ? ";

        return $this->db()->fetchColumn($sql, [$courseId]) ?: 0;
    }

    public function getMinSeqByCourseId($courseId)
    {
        $sql = "SELECT MIN(seq) FROM {$this->table()} WHERE courseId = ? ";

        return $this->db()->fetchColumn($sql, [$courseId]) ?: 0;
    }

    public function getNextTaskByCourseIdAndSeq($courseId, $seq)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE seq > ? and courseId = ?  ORDER BY seq ASC LIMIT 1 ";

        return $this->db()->fetchAssoc($sql, [$seq, $courseId]);
    }

    public function getPreTaskByCourseIdAndSeq($courseId, $seq)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE seq < ? and courseId = ?  ORDER BY seq DESC LIMIT 1 ";

        return $this->db()->fetchAssoc($sql, [$seq, $courseId]);
    }

    public function getByCopyId($copyId)
    {
        return $this->getByFields(['copyId' => $copyId]);
    }

    public function getByCourseIdAndCopyId($courseId, $copyId)
    {
        return $this->getByFields(['courseId' => $courseId, 'copyId' => $copyId]);
    }

    public function findByChapterId($chapterId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE categoryId = ? ORDER BY seq ASC ";

        return $this->db()->fetchAll($sql, [$chapterId]) ?: [];
    }

    public function countByChpaterId($chapterId)
    {
        $sql = "SELECT count(*) FROM {$this->table()} WHERE categoryId = ?";

        return $this->db()->fetchColumn($sql, [$chapterId]);
    }

    public function getByChapterIdAndMode($chapterId, $mode)
    {
        $sql = "SELECT * FROM {$this->table()}  WHERE `categoryId`= ? AND `mode` = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$chapterId, $mode]);
    }

    public function getByCourseIdAndSeq($courseId, $sql)
    {
        return $this->getByFields(['courseId' => $courseId, 'seq' => $sql]);
    }

    /**
     * 返回过去直播过的教学计划ID.
     *
     * @return array<int>
     */
    public function findPastLivedCourseSetIds()
    {
        $time = time();
        $sql
            = "SELECT fromCourseSetId, max(startTime) as startTime
                 FROM {$this->table()}
                 WHERE endTime < {$time} AND status='published' AND type = 'live'
                 GROUP BY fromCourseSetId
                 ORDER BY startTime DESC
                 ";

        return $this->db()->fetchAll($sql);
    }

    public function getTaskByCourseIdAndActivityId($courseId, $activityId)
    {
        return $this->getByFields(['courseId' => $courseId, 'activityId' => $activityId]);
    }

    public function findByCourseIdAndIsFree($courseId, $isFree)
    {
        return $this->findByFields(['courseId' => $courseId, 'isFree' => $isFree]);
    }

    public function findByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        if (empty($courseIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $parmaters = array_merge([$copyId], $courseIds);

        $sql = "SELECT * FROM {$this->table()} WHERE copyId= ? AND courseId IN ({$marks})";

        return $this->db()->fetchAll($sql, $parmaters) ?: [];
    }

    public function findByCopyIdSAndLockedCourseIds($copyIds, $courseIds)
    {
        if (empty($courseIds) || empty($copyIds)) {
            return [];
        }

        $copyIdMarks = str_repeat('?,', count($copyIds) - 1).'?';
        $courseIdMarks = str_repeat('?,', count($courseIds) - 1).'?';

        $parmaters = array_merge($copyIds, $courseIds);

        $sql = "SELECT * FROM {$this->table()} WHERE copyId IN ({$copyIdMarks}) AND courseId IN ({$courseIdMarks})";

        return $this->db()->fetchAll($sql, $parmaters) ?: [];
    }

    public function countLessonsWithMultipleTasks($courseId)
    {
        $sql = "SELECT count(*) AS num FROM {$this->table()} WHERE courseId = ? GROUP BY categoryId HAVING num > 1;";

        return $this->db()->fetchAll($sql, [$courseId]) ?: [];
    }

    public function analysisTaskDataByTime($startTime, $endTime)
    {
        $conditions = [
            'createdTime_GE' => $startTime,
            'createdTime_LT' => $endTime,
        ];

        $builder = $this->createQueryBuilder($conditions)
            ->select("count(id) AS count, from_unixtime(createdTime, '%Y-%m-%d') AS date")
            ->from($this->table, $this->table)
            ->groupBy('date')
            ->addOrderBy('date', 'asc');

        return $builder->execute()->fetchAll();
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'orderbys' => [
                'seq',
                'startTime',
                'createdTime',
                'updatedTime',
                'id',
                'number',
            ],
            'conditions' => [
                'id = :id',
                'id IN ( :ids )',
                'id NOT IN (:excludeIds)',
                'courseId = :courseId',
                'courseId IN ( :courseIds )',
                'title LIKE :titleLike',
                'fromCourseSetId = :fromCourseSetId',
                'fromCourseSetId IN (:fromCourseSetIds)',
                'status =:status',
                'mediaSource = :mediaSource',
                'type = :type',
                'isFree =:isFree',
                'type IN ( :types )',
                'type NOT IN ( :typesNotIn )',
                'seq >= :seq_GE',
                'seq > :seq_GT',
                'seq < :seq_LT',
                'seq >= :seq_GTE',
                'seq <= :seq_LTE',
                'startTime >= :startTime_GE',
                'createdTime >= :createdTime_GE',
                'createdTime <= :createdTime_LE',
                'startTime > :startTime_GT',
                'startTime <= :startTime_LE',
                'updatedTime >= :updatedTime_GE',
                'updatedTime <= :updatedTime_LE',
                'endTime > :endTime_GT',
                'endTime < :endTime_LT',
                'endTime <= :endTime_GE',
                'categoryId = :categoryId',
                'categoryId IN (:categoryIds)',
                'activityId = :activityId',
                'mode = :mode',
                'mode IN ( :modes )',
                'isOptional = :isOptional',
                'copyId = :copyId',
                'copyId IN (:copyIds)',
                /*S2B2C 同步ID*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
        ];
    }
}
