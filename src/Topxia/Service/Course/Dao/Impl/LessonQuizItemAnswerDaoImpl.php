<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonQuizItemAnswerDao;

class LessonQuizItemAnswerDaoImpl extends BaseDao implements LessonQuizItemAnswerDao
{
    protected $table = 'lesson_quiz_item_answer';

    public function addLessonQuizItemAnswer($lessonQuizItemAnswerInfo)
    {
        $affected = $this->getConnection()->insert($this->table, $lessonQuizItemAnswerInfo);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lessonQuizItemAnswer error.');
        }
        return $this->getLessonQuizItemAnswer($this->getConnection()->lastInsertId());
    }

    public function getCorrectAnswersCountByUserIdAndQuizId($userId, $quizId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE userId = ? AND quizId = ? AND isCorrect = 1";
        return $this->getConnection()->fetchColumn($sql, array($userId, $quizId));
    }

    public function deleteLessonQuizItemAnswer($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getLessonQuizItemAnswerByQuizIdAndItemIdAndUserId($quizId, $itemId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE quizId = ? AND itemId = ? AND userId =?";
        return $this->getConnection()->fetchAssoc($sql, array($quizId, $itemId, $userId));
    }

    public function getLessonQuizItemAnswer($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function deleteLessonQuizItemAnswersByUserIdAndQuizId($userId, $quizId)
    {
        return $this->getConnection()->delete($this->table, array('userId' => $userId, 'quizId' => $quizId));
    }

}