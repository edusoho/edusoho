<?php
namespace Topxia\Service\Course;

interface QuizService
{



    public function getQuizItem($lessonQuizItemId);

    public function getUserLessonQuiz($courseId, $lessonId, $userId);


    public function createItem(array $item);

    public function updateItem($id, $fields);

    public function deleteItem($id);


    public function getQuiz($id);

    public function createLessonQuiz($courseId, $lessonId, $itemIds);

    public function deleteQuiz($id);

    public function findLessonQuizItems($courseId, $lessonId);

    public function findLessonQuizItemIds($courseId, $lessonId);

    

    public function findQuizItemsInLessonQuiz($lessonQuizId);

    public function answerQuizItem($lessonQuizId, $itemId, $answerContent);

    public function submitQuizResult($quizId);

}