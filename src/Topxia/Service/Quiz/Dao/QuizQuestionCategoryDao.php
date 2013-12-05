<?php

namespace Topxia\Service\Quiz\Dao;

interface QuizQuestionCategoryDao
{
    public function addQuestionCategory($category);

    public function deleteQuestionCategory($id);

    public function getQuestionCategory($id);

    public function findQuestionCategorysByIds(array $ids);

    public function deleteQuestionCategorysByIds(array $ids);

    public function searchQuestionCategorysCount($conditions);

    public function searchQuestionCategorys($conditions, $orderBy, $start, $limit);
}