<?php

namespace Topxia\Service\Question\Dao;

interface CategoryDao
{
    public function getCategory($id);

    public function addCategory($fields);

    public function updateCategory($id, $fields);

    public function deleteCategory($id);

    public function findCategoriesByTarget($target, $start, $limit);

    public function findCategoriesByIds($ids);

    public function getCategorysCountByTarget($target);

}