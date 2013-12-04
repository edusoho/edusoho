<?php

namespace Topxia\Service\QuizQuestion\Dao;

interface QuizQuestionDao
{
    public function addQuestion($questions);

    public function deleteQuestion($id);

    public function getQuestion($id);

    public function findQuestionByIds(array $ids);

    public function deleteQuestionByIds(array $ids);

    public function searchQuestionCount($conditions);

    public function searchQuestion($conditions, $orderBy, $start, $limit);
}