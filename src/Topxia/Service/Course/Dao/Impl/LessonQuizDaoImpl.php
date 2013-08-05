<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonQuizDao;

class LessonQuizDaoImpl extends BaseDao implements LessonQuizDao
{
    protected $table = 'lesson_quiz';

    public function addLessonQuiz($lessonQuizInfo)
    {
        $affected = $this->getConnection()->insert($this->table, $lessonQuizInfo);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lessonQuiz error.');
        }
        return $this->getLessonQuiz($this->getConnection()->lastInsertId());
    }

    public function deleteLessonQuiz($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }
    
    public function getLessonQuizByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND lessonId = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($courseId, $lessonId, $userId));
    }
    
    public function getLessonQuiz($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function updateLessonQuiz($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getLessonQuiz($id);
    }

}