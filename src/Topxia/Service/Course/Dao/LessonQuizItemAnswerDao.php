<?php

namespace Topxia\Service\Course\Dao;

interface LessonQuizItemAnswerDao
{
    public function addLessonQuizItemAnswer($lessonQuizItemAnswerInfo);

    public function getLessonQuizItemAnswer($id);

    public function getLessonQuizItemAnswerByQuizIdAndItemIdAndUserId($quizId, $itemId, $userId);

    public function deleteLessonQuizItemAnswer($id);

    public function getCorrectAnswersCountByUserIdAndQuizId($userId, $quizId);

    public function deleteLessonQuizItemAnswersByUserIdAndQuizId($userId, $quizId);
}