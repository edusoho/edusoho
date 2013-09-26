<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseQuizDao;

class CourseQuizDaoImpl extends BaseDao implements CourseQuizDao
{
    protected $table = 'course_quiz';

    public function getQuiz($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addQuiz($lessonQuizInfo)
    {
        $affected = $this->getConnection()->insert($this->table, $lessonQuizInfo);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lessonQuiz error.');
        }
        return $this->getQuiz($this->getConnection()->lastInsertId());
    }

    public function deleteQuiz($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
    
    public function getQuizByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND lessonId = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($courseId, $lessonId, $userId));
    }

    public function updateQuiz($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getQuiz($id);
    }

}