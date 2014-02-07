<?php
namespace Topxia\Service\Quiz;

interface QuestionService
{
    /**
     *  quiz_question
     */

    public function getQuestion($id);

    public function createQuestion($question);

    public function updateQuestion($id, $question);

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit);

    public function searchQuestionCount(array $conditions);

    public function findQuestionsByIds(array $ids);

    /**
    *  
    **/
    public function findQuestionsByTypeAndTypeIds($type, $ids);

    public function findQuestionsCountByTypeAndTypeIds($type, $ids);



}