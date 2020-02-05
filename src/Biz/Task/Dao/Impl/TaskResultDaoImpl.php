<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TaskResultDaoImpl extends GeneralDaoImpl implements TaskResultDao
{
    protected $table = 'course_task_result';

    public function findTaskresultsByTaskId($taskId)
    {
        return $this->findByFields(array(
            'courseTaskId' => $taskId,
        ));
    }

    public function analysisCompletedTaskDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) AS count, from_unixtime(finishedTime, '%Y-%m-%d') AS date FROM
            {$this->table} WHERE finishedTime >= ? AND finishedTime < ? GROUP BY date ORDER BY date ASC";

        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function findByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? ";

        return $this->db()->fetchAll($sql, array($courseId, $userId)) ?: array();
    }

    public function getByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getByFields(array(
            'activityId' => $activityId,
            'userId' => $userId,
        ));
    }

    public function getByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getByFields(array(
            'userId' => $userId,
            'courseTaskId' => $taskId,
        ));
    }

    public function findByTaskIdsAndUserId($taskIds, $userId)
    {
        $marks = str_repeat('?,', count($taskIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE courseTaskId IN ({$marks}) and userId = ? order by id desc;";

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

    public function deleteByTaskId($taskId)
    {
        return $this->db()->delete($this->table, array('courseTaskId' => $taskId));
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table, array('courseId' => $courseId));
    }

    public function countLearnNumByTaskId($taskId)
    {
        $sql = "SELECT count(id) FROM {$this->table()} WHERE courseTaskId = ? ";

        return $this->db()->fetchColumn($sql, array($taskId));
    }

    public function findFinishedTimeByCourseIdGroupByUserId($courseId)
    {
        //已发布task总数
        $sql = "SELECT count(id) FROM course_task WHERE courseId = ? AND status='published'";
        $totalTaskCount = $this->db()->fetchColumn($sql, array($courseId));

        if ($totalTaskCount <= 0) {
            return array();
        }

        $sql = "SELECT max(finishedTime) AS finishedTime, count(courseTaskId) AS taskCount, userId FROM {$this->table()}
                WHERE courseId = ? and status='finish' AND userId IN (SELECT userId FROM course_member WHERE courseId = ? AND role='student' )
                group by userId HAVING taskCount >= ?";

        return $this->db()->fetchAll($sql, array($courseId, $courseId, $totalTaskCount)) ?: array();
    }

    public function sumLearnTimeByCourseIdAndUserId($courseId, $userId)
    {
        $sql = 'SELECT sum(TIME) FROM `course_task_result` WHERE `status`= ? AND  `courseId` = ? AND `userId`= ?';

        return $this->db()->fetchColumn($sql, array('finish', $courseId, $userId));
    }

    public function getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskId)
    {
        $builder = $this->createQueryBuilder(array('courseTaskId' => $courseTaskId))
            ->select('sum(time) AS learnedTime')
            ->groupBy('courseTaskId');

        return $builder->execute()->fetchColumn();
    }

    public function getWatchTimeByCourseIdGroupByCourseTaskId($courseTaskId)
    {
        $builder = $this->createQueryBuilder(array('courseTaskId' => $courseTaskId))
            ->select('sum(watchTime) AS watchTime')
            ->groupBy('courseTaskId');

        return $builder->execute()->fetchColumn();
    }

    public function countFinishedTasksByUserIdAndCourseIdsGroupByCourseId($userId, $courseIds)
    {
        $builder = $this->createQueryBuilder(array('userId' => $userId, 'courseIds' => $courseIds))
            ->select('count(id) as count, courseId')
            ->groupBy('courseId');

        return $builder->execute()->fetchAll();
    }

    public function countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $courseId)
    {
        $sql = 'SELECT COUNT(ctr.id) FROM course_task AS ct JOIN course_task_result ctr ON ct.id = ctr.courseTaskId where userId = ? AND ct.courseId = ? AND ctr.status = \'finish\' AND ct.isOptional = 0';

        return $this->db()->fetchColumn($sql, array($userId, $courseId)) ?: 0;
    }

    public function sumCourseSetLearnedTimeByTaskIds($taskIds)
    {
        if (empty($taskIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($taskIds) - 1).'?';
        $sql = "select sum(`time`) from {$this->table()} where `courseTaskId` in ({$marks})";

        return $this->db()->fetchColumn($sql, $taskIds);
    }

    public function countTaskNumGroupByUserId($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('count(id) as count, userId')
            ->groupBy('userId');

        return $builder->execute()->fetchAll();
    }

    public function declares()
    {
        return array(
            'orderbys' => array('createdTime', 'updatedTime', 'finishedTime'),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'status =:status',
                'userId =:userId',
                'userId IN ( :userIds )',
                'courseId =:courseId',
                'type =: type',
                'courseTaskId IN (:courseTaskIds)',
                'courseId IN ( :courseIds )',
                'activityId =:activityId',
                'courseTaskId = :courseTaskId',
                'createdTime >= :createdTime_GE',
                'createdTime <= :createdTime_LE',
                'createdTime < :createdTime_LT',
                'finishedTime >= :finishedTime_GE',
                'finishedTime <= :finishedTime_LE',
                'finishedTime < :finishedTime_LT',
            ),
        );
    }
}
