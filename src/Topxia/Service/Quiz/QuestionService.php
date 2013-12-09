<?php
namespace Topxia\Service\Quiz;

interface QuestionService
{

    public function getQuestion($lessonQuizItemId);

    public function addQuestion($type,$question);

    public function getQuestionTarget($courseId);

    public function searchQuestionCount(array $conditions);

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit);

}