<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseQuizItemAnswerDao;

class CourseQuizItemAnswerDaoImpl extends BaseDao implements CourseQuizItemAnswerDao
{
    protected $table = 'course_quiz_item_answer';

    public function addAnswer($answerInfo)
    {
        $affected = $this->getConnection()->insert($this->table, $lessonQuizItemAnswerInfo);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lessonQuizItemAnswer error.');
        }
        return $this->getAnswer($this->getConnection()->lastInsertId());
    }

    public function getCorrectAnswersCountByUserIdAndQuizId($userId, $quizId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE userId = ? AND quizId = ? AND isCorrect = 1";
        return $this->getConnection()->fetchColumn($sql, array($userId, $quizId));
    }

    public function getAnswerByQuizIdAndItemIdAndUserId($quizId, $itemId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE quizId = ? AND itemId = ? AND userId =?";
        return $this->getConnection()->fetchAssoc($sql, array($quizId, $itemId, $userId));
    }

    public function getAnswer($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function deleteAnswersByUserIdAndQuizId($userId, $quizId)
    {
        return $this->getConnection()->delete($this->table, array('userId' => $userId, 'quizId' => $quizId));
    }

}