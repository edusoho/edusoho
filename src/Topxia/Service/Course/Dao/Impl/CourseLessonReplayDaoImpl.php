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

    public function deleteLessonReplayByLessonId($lessonId)
    {
        $result = $this->getConnection()->delete($this->table, array('lessonId' => $lessonId));
        $this->clearCached();
        return $result;
    }

    public function getCourseLessonReplayByLessonId($lessonId)
    {
        $that = $this;

        return $this->fetchCached("lessonId:{$lessonId}", $lessonId, function ($lessonId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE lessonId = ? ORDER BY replayId ASC";
            return $that->getConnection()->fetchAll($sql, array($lessonId));
        }

        );
    }

    public function deleteLessonReplayByCourseId($courseId)
    {
        $result = $this->getConnection()->delete($this->table, array('courseId' => $courseId));
        $this->clearCached();
        return $result;
    }

    public function getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:lessonId:{$lessonId}", $courseId, $lessonId, function ($courseId, $lessonId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE courseId=? AND lessonId = ? ";
            return $that->getConnection()->fetchAssoc($sql, array($courseId, $lessonId));
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

    public function updateCourseLessonReplayByLessonId($lessonId, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('lessonId' => $lessonId));
        $this->clearCached();
        return $this->getCourseLessonReplayByLessonId($lessonId);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, 'course_lesson_replay')
                        ->andWhere('courseId = :courseId');
        return $builder;
    }
}
