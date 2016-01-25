<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonLearnDao;

class LessonLearnDaoImpl extends BaseDao implements LessonLearnDao
{
    protected $table = 'course_lesson_learn';

    public function getLearn($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function getLearnByUserIdAndLessonId($userId, $lessonId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:lessonId:{$lessonId}", $userId, $lessonId, function ($userId, $lessonId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId=? AND lessonId=?";
            return $that->getConnection()->fetchAssoc($sql, array($userId, $lessonId)) ?: null;
        }

        );
    }

    public function findLearnsByUserIdAndCourseId($userId, $courseId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:courseId:{$courseId}", $userId, $courseId, function ($userId, $courseId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId=? AND courseId=?";
            return $that->getConnection()->fetchAll($sql, array($userId, $courseId)) ?: array();
        }

        );
    }

    public function findLearnsByUserIdAndCourseIdAndStatus($userId, $courseId, $status)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:courseId:{$courseId}:status:{$status}", $userId, $courseId, $status, function ($userId, $courseId, $status) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId=? AND courseId=? AND status = ?";
            return $that->getConnection()->fetchAll($sql, array($userId, $courseId, $status)) ?: array();
        }

        );
    }

    public function getLearnCountByUserIdAndCourseIdAndStatus($userId, $courseId, $status)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:courseId:{$courseId}:status:{$status}:count", $userId, $courseId, $status, function ($userId, $courseId, $status) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE userId = ? AND courseId = ? AND status = ?";
            return $that->getConnection()->fetchColumn($sql, array($userId, $courseId, $status));
        }

        );
    }

    public function findLearnsByLessonId($lessonId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE lessonId = ? ORDER BY startTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($lessonId));
    }

    public function findLearnsCountByLessonId($lessonId)
    {
        $that = $this;

        return $this->fetchCached("lessonId:{$lessonId}:count", $lessonId, function ($lessonId) use ($that) {
            $sql = "SELECT COUNT(*) FROM {$that->getTable()} WHERE lessonId = ?";
            return $that->getConnection()->fetchColumn($sql, array($lessonId));
        }

        );
    }

    public function findLatestFinishedLearns($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE status = 'finished' ORDER BY finishedTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql);
    }

    public function addLearn($learn)
    {
        $affected = $this->getConnection()->insert($this->table, $learn);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert learn error.');
        }

        return $this->getLearn($this->getConnection()->lastInsertId());
    }

    public function updateLearn($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getLearn($id);
    }

    public function deleteLearnsByLessonId($lessonId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE lessonId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($lessonId));
        $this->clearCached();
        return $result;
    }

    public function searchLearnCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('count(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchLearnTime($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('sum(learnTime)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchWatchTime($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('sum(watchTime)');

        return $builder->execute()->fetchColumn(0);
    }

    public function searchLearns($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
                        ->select('*')
                        ->orderBy($orderBy[0], $orderBy[1])
                        ->setFirstResult($start)
                        ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['targetType'])) {
            $builder = $this->createDynamicQueryBuilder($conditions)
                            ->from($this->table, $this->table)
                            ->andWhere("status = :status")
                            ->andWhere("finishedTime >= :startTime")
                            ->andWhere("finishedTime <= :endTime");
        } else {
            $builder = $this->createDynamicQueryBuilder($conditions)
                            ->from($this->table, $this->table)
                            ->andWhere("status = :status")
                            ->andWhere("userId = :userId")
                            ->andWhere("lessonId = :lessonId")
                            ->andWhere("courseId = :courseId")
                            ->andWhere("finishedTime >= :startTime")
                            ->andWhere("finishedTime <= :endTime");
        }

        $builder->andWhere("courseId IN (:courseIds)")
                ->andWhere('lessonId IN (:lessonIds)');

        return $builder;
    }

    public function analysisLessonFinishedDataByTime($startTime, $endTime)
    {
        $that = $this;

        return $this->fetchCached("startTime:{$startTime}:endTime:{$endTime}:count", $startTime, $endTime, function ($startTime, $endTime) use ($that) {
            $sql = "SELECT count(id) as count, from_unixtime(finishedTime,'%Y-%m-%d') as date FROM `{$that->getTable()}` WHERE`finishedTime`>=? AND `finishedTime`<=? AND `status`='finished'  group by from_unixtime(`finishedTime`,'%Y-%m-%d') order by date ASC ";
            return $that->getConnection()->fetchAll($sql, array($startTime, $endTime));
        }

        );
    }

    public function deleteLearn($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }
}
