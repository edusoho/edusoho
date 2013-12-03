<?php

namespace Topxia\Service\Quiz\Dao;

interface QuestionsDao
{
    public function addQuestion($questions);

    public function deleteQuestion($id);

    public function getQuestion($id);

    public function findQuestionsByIds(array $ids);

    public function deleteQuestionsByIds(array $ids);

    public function searchQuestionCount($conditions);

    public function searchQuestions($conditions, $orderBy, $start, $limit);
}