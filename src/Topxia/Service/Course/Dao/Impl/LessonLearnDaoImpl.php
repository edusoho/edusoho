<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonLearnDao;

class LessonLearnDaoImpl extends BaseDao implements LessonLearnDao
{
    protected $table = 'course_lesson_learn';

	public function getLearn($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function getLearnByUserIdAndLessonId($userId, $lessonId)
	{
        $sql ="SELECT * FROM {$this->table} WHERE userId=? AND lessonId=?";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $lessonId)) ? : null;
	}

	public function findLearnsByUserIdAndCourseId($userId, $courseId)
	{
        $sql ="SELECT * FROM {$this->table} WHERE userId=? AND courseId=?";
        return $this->getConnection()->fetchAll($sql, array($userId, $courseId)) ? : array();
	}

	public function findLearnsByUserIdAndCourseIdAndStatus($userId, $courseId, $status)
	{
        $sql ="SELECT * FROM {$this->table} WHERE userId=? AND courseId=? AND status = ?";
        return $this->getConnection()->fetchAll($sql, array($userId, $courseId, $status)) ? : array();
	}

	public function getLearnCountByUserIdAndCourseIdAndStatus($userId, $courseId, $status)
	{
        $sql ="SELECT COUNT(*) FROM {$this->table} WHERE userId = ? AND courseId = ? AND status = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $courseId, $status));
	}

    public function findLearnsByLessonId($lessonId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE lessonId = ? ORDER BY startTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($lessonId));
    }

    public function findLearnsCountByLessonId($lessonId)
    {
        $sql ="SELECT COUNT(*) FROM {$this->table} WHERE lessonId = ?";
        return $this->getConnection()->fetchColumn($sql, array($lessonId));
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
        if ($affected <= 0) {
            throw $this->createDaoException('Insert learn error.');
        }
        return $this->getLearn($this->getConnection()->lastInsertId());
	}

	public function updateLearn($id, $fields)
	{
        $id = $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getLearn($id);
	}

    public function deleteLearnsByLessonId($lessonId)
    {
        $sql = "DELETE FROM {$this->table} WHERE lessonId = ?";
        return $this->getConnection()->executeUpdate($sql, array($lessonId));
    }
}