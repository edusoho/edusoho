<?php

namespace Topxia\Service\Quiz\Dao;

interface QuizQuestionDao
{
    public function addQuestion($questions);

    public function updateQuestion($id, $fields);

    public function deleteQuestion($id);

    public function deleteQuestionsByParentId($id);

    public function getQuestion($id);

    public function findQuestionsByIds(array $ids);

    public function deleteQuestionByIds(array $ids);

    public function findQuestionsByTypeAndTypeIds($type,$ids);

    public function searchQuestionCount($conditions);

    public function searchQuestion($conditions, $orderBy, $start, $limit);
}