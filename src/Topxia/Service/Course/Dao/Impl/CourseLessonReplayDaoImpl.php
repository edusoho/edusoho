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

	private function getTablename()
    {
        return self::TABLENAME;
    }
}