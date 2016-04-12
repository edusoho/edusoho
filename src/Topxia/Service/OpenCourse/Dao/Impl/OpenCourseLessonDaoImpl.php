<?php

namespace Topxia\Service\OpenCourse\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\OpenCourse\Dao\OpenCourseLessonDao;

class OpenCourseLessonDaoImpl extends BaseDao implements OpenCourseLessonDao
{
    protected $table = 'open_course_lesson';

    public function getLesson($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function findLessonsByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findLessonsByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId = ? ORDER BY seq ASC";
            return $that->getConnection()->fetchAll($sql, array($courseId));
        }

        );
    }

    public function searchLessons($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime', 'startTime', 'seq'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchLessonCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addLesson($lesson)
    {
        $lesson['createdTime'] = time();
        $lesson['updatedTime'] = $lesson['createdTime'];
        $affected              = $this->getConnection()->insert($this->table, $lesson);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lesson error.');
        }

        return $this->getLesson($this->getConnection()->lastInsertId());
    }

    public function updateLesson($id, $fields)
    {
        $fields['updatedTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getLesson($id);
    }

    public function deleteLesson($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function getLessonMaxSeqByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:max:seq", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT MAX(seq) FROM {$that->getTable()} WHERE  courseId = ?";
            return $that->getConnection()->fetchColumn($sql, array($courseId));
        }

        );
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('id = :lessonId')
            ->andWhere('id NOT IN (:lessonIdNotIn)')
            ->andWhere('courseId = :courseId')
            ->andWhere('updatedTime >= :updatedTime_GE')
            ->andWhere('status = :status')
            ->andWhere('type = :type')
            ->andWhere('free = :free')
            ->andWhere('userId = :userId')
            ->andWhere('mediaId = :mediaId')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('endTime < :endTimeLessThan')
            ->andWhere('startTime <= :startTimeLessThan')
            ->andWhere('endTime > :endTimeGreaterThan')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('copyId = :copyId')
            ->andWhere('courseId IN ( :courseIds )');

        if (isset($conditions['notLearnedIds'])) {
            $builder->andWhere('id NOT IN ( :notLearnedIds)');
        }

        return $builder;
    }
}
