<?php

namespace Biz\QuestionBank\Service;

interface CategoryService
{
    public function getCategory($id);

    public function findCategoriesByIds($ids);

    public function getCategoryStructureTree();

    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    public function deleteCategory($id);

    public function waveCategoryBankNum($id, $diff);

    public function getCategoryTree();

    public function getCategoryAndBankMixedTree();

    public function findAllCategories();

    public function findAllCategoriesByParentId($parentId);

    public function findAllChildrenIdsByParentId($parentId);

    public function sortCategories($ids);
}
