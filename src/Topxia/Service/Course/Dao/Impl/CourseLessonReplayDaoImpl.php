<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseLessonReplayDao;

class CourseLessonReplayDaoImpl extends BaseDao implements CourseLessonReplayDao
{
    protected $table = 'course_lesson_replay';

    public function addCourseLessonReplay($courseLessonReplay)
    {
        $affected = $this->getConnection()->insert($this->table, $courseLessonReplay);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course_lesson_replay error.');
        }

        return $this->getCourseLessonReplay($this->getConnection()->lastInsertId());
    }

    public function getCourseLessonReplay($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function deleteLessonReplayByLessonId($lessonId, $lessonType = 'live')
    {
        $result = $this->getConnection()->delete($this->table, array('lessonId' => $lessonId, 'type' => $lessonType));
        $this->clearCached();
        return $result;
    }

    public function getCourseLessonReplayByLessonId($lessonId, $lessonType = 'live')
    {
        $that = $this;

        return $this->fetchCached("lessonId:{$lessonId}:lessonType:{$lessonType}", $lessonId, $lessonType, function ($lessonId, $lessonType) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE lessonId = ? AND type = ? ORDER BY replayId ASC";
            return $that->getConnection()->fetchAll($sql, array($lessonId, $lessonType));
        }

        );
    }

    public function deleteLessonReplayByCourseId($courseId, $lessonType = 'live')
    {
        $result = $this->getConnection()->delete($this->table, array('courseId' => $courseId, 'type' => $lessonType));
        $this->clearCached();
        return $result;
    }

    public function getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live')
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:lessonId:{$lessonId}:lessonType:{$lessonType}", $courseId, $lessonId,$lessonType, function ($courseId, $lessonId, $lessonType) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId=? AND lessonId = ? AND type = ? ";
            return $that->getConnection()->fetchAssoc($sql, array($courseId, $lessonId, $lessonType));
        }

        );
    }

    public function searchCourseLessonReplayCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchCourseLessonReplays($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('replayId', 'createdTime'));
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function deleteCourseLessonReplay($id)
    {
        $result = $this->getConnection()->delete($this->getTable(), array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function updateCourseLessonReplay($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getCourseLessonReplay($id);
    }

    public function updateCourseLessonReplayByLessonId($lessonId, $fields, $lessonType = 'live')
    {
        $this->getConnection()->update($this->table, $fields, array('lessonId' => $lessonId, 'type' => $lessonType));
        $this->clearCached();
        return $this->getCourseLessonReplayByLessonId($lessonId);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_lesson_replay')
            ->andWhere('courseId = :courseId')
            ->andWhere('lessonId = :lessonId')
            ->andWhere('hidden = :hidden')
            ->andWhere('type = :type');
        return $builder;
    }
}
