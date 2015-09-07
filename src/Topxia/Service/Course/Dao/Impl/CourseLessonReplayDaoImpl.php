<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseLessonReplayDao;

class CourseLessonReplayDaoImpl extends BaseDao implements CourseLessonReplayDao
{
	public function addCourseLessonReplay($courseLessonReplay)
	{
		$affected = $this->getConnection()->insert(self::TABLENAME, $courseLessonReplay);
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
			$sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
    	    return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    	});
	}

	public function deleteLessonReplayByLessonId($lessonId)
	{
		$result = $this->getConnection()->delete(self::TABLENAME, array('lessonId' => $lessonId));
		$this->clearCached();
		return $result;
	}

	public function getCourseLessonReplayByLessonId($lessonId)
	{
		$that = $this;

        return $this->fetchCached("lessonId:{$lessonId}", $lessonId, function ($lessonId) use ($that) {
	        $sql ="SELECT * FROM {$this->getTablename()} WHERE lessonId = ? ORDER BY replayId ASC";
	        return $this->getConnection()->fetchAll($sql, array($lessonId));
	    });
	}

	public function deleteLessonReplayByCourseId($courseId)
	{
		$result = $this->getConnection()->delete(self::TABLENAME, array('courseId' => $courseId));
		$this->clearCached();
		return $result;
	}

	public function getCourseLessonReplayByCourseIdAndLessonId($courseId, $lessonId)
	{
		$that = $this;

        return $this->fetchCached("courseId:{$courseId}:lessonId:{$lessonId}", $courseId, $lessonId, function ($courseId, $lessonId) use ($that) {
			$sql ="SELECT * FROM {$this->getTablename()} WHERE courseId=? AND lessonId = ? ";
	        return $this->getConnection()->fetchAssoc($sql, array($courseId,$lessonId));
	    });
	}

	protected function getTablename()
    {
        return self::TABLENAME;
    }
}