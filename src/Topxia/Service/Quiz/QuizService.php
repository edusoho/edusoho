<?php
namespace Topxia\Service\Quiz;

interface QuizService
{

    public function getQuestion($lessonQuizItemId);

    public function findLessonsByCourseId($courseId);

    public function searchQuestionCount(array $conditions);

    public function searchQuestions(array $conditions, array $orderBy, $start, $limit);
}