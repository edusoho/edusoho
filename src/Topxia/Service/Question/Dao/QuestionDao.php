<?php

namespace Topxia\Service\Question\Dao;

interface QuestionDao
{
    public function getQuestion($id);

    public function findQuestionsByIds(array $ids);

    public function findQuestionsByParentId($id);

    public function findQuestionsByParentIds(array $ids);

    public function searchQuestions($conditions, $sort, $start, $limit);

    public function searchQuestionsCount($conditions);

    public function findQuestionsCountByParentId($parentId);

    public function addQuestion($fields);

    public function updateQuestion($id, $fields);

    public function deleteQuestion($id);

    public function deleteQuestionsByParentId($id);

    public function updateQuestionCountByIds($ids, $status);
}