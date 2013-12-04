<?php
namespace Topxia\Service\QuizQuestion;

interface QuizQuestionService
{

    public function getQuestion($lessonQuizItemId);

    public function searchQuestionCount(array $conditions);

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit);
}