<?php

namespace Biz\Question\Service;

interface CategoryService
{
    public function getCategory($id);

    public function findCategoriesByIds($ids);

    public function getCategoryStructureTree($bankId);

    public function getCategoryTree($bankId);

    public function findCategories($bankId);

    public function batchCreateCategory($bankId, $parentId, $names);

    public function updateCategory($id, $fields);

    public function deleteCategory($id);
}
