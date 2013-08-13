<?php

namespace Topxia\Service\Course\Dao;

interface CourseQuizItemAnswerDao
{
    public function addAnswer($answerInfo);

    public function getAnswer($id);

    public function getAnswerByQuizIdAndItemIdAndUserId($quizId, $itemId, $userId);
    
    public function getCorrectAnswersCountByUserIdAndQuizId($userId, $quizId);

    public function deleteAnswersByUserIdAndQuizId($userId, $quizId);
}