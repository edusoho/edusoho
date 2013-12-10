<?php

namespace Topxia\Service\Quiz\Dao;

interface QuizQuestionCategoryDao
{
    public function addCategory($category);

    public function deleteCategory($id);

    public function getCategory($id);

    public function updateCategory($id, $fields);

    public function findCategorysByIds(array $ids);

    public function deleteCategorysByIds(array $ids);

    public function searchCategoryCount($conditions);

    public function searchCategory($conditions, $orderBy, $start, $limit);
}