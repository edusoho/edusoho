<?php

namespace Biz\Question\Dao;

interface QuestionDao
{
    public function findQuestionsByIds(array $ids);

    public function findQuestionsByParentId($id);

    public function deleteSubQuestions($parentId);
}
