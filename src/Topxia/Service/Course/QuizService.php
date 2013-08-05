<?php
namespace Topxia\Service\Course;

interface QuizService
{

    public function createLessonQuizItem($courseId, $lessonId, $itemInfo);

    public function getLessonQuizItem($lessonQuizItemId);

    public function getUserLessonQuiz($courseId, $lessonId, $userId);

    public function editLessonQuizItem($lessonQuizItemId, $fields);

    public function deleteLessonQuizItem($id);

    public function deleteLessonQuiz($id);

    public function findLessonQuizItems($courseId, $lessonId);

    public function findLessonQuizItemIds($courseId, $lessonId);

    public function createLessonQuiz($courseId, $lessonId, $itemIds);

    public function findQuizItemsInLessonQuiz($lessonQuizId);

    public function answerLessonQuizItem($lessonQuizId, $itemId, $answerContent);

    public function checkUserLessonQuizResult($quizId);

}