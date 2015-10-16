<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseLessonReplayDao;

class CourseLessonReplayDaoImpl extends BaseDao implements CourseLessonReplayDao
{
	public function addCourseLessonReplay($courseLessonReplay)
	{
		$affected = $this->getConnection()->insert(self::TABLENAME, $courseLessonReplay);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course_lesson_replay error.');
        }
        return $this->getCourseLessonReplay($this->getConnection()->lastInsertId());
	}

	public function getCourseLessonReplay($id)
	{
		$sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function deleteLessonReplayByLessonId($lessonId)
	{
		return $this->getConnection()->delete(self::TABLENAME, array('lessonId' => $lessonId));
	}

	public function getCourseLessonReplayByLessonId($lessonId)
	{
        $sql ="SELECT * FROM {$this->getTablename()} WHERE lessonId = ? ORDER BY replayId ASC";
        return $this->getConnection()->fetchAll($sql, array($lessonId));
	}

	public function deleteLessonReplayByCourseId($courseId)
	{
		return $this->getConnection()->delete(self::TABLENAME, array('courseId' => $courseId));
	}

	public function getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId)
	{
		$sql ="SELECT * FROM {$this->getTablename()} WHERE courseId=? AND lessonId = ? ";
        return $this->getConnection()->fetchAssoc($sql, array($courseId,$lessonId));
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
        return $builder->execute()->fetchAll() ? : array();

	}

	public function deleteCourseLessonReplay($id)
	{
		return $this->getConnection()->delete($this->getTablename(), array('id' => $id));
	}

	protected function getTablename()
    {
        return self::TABLENAME;
    }

    protected function _createSearchQueryBuilder($conditions)
    {   
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_lesson_replay')
            ->andWhere('courseId = :courseId');
        return $builder;
    }
}