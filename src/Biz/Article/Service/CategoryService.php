<?php

namespace Biz\Article\Service;

interface CategoryService
{
    public function getCategory($id);

    public function getCategoryByCode($code);

    public function getCategoryTree($isPublished = false);

    public function getCategoryStructureTree();

    public function sortCategories($ids);

    public function getCategoryByParentId($parentId);

    public function findAllCategoriesByParentId($parentId);

    public function findCategoryChildrenIds($id);

    public function findCategoriesByIds(array $ids);

    public function findAllCategories();

    public function findCategoryBreadcrumbs($categoryId);

    public function isCategoryCodeAvailable($code, $exclude = null);

    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    public function deleteCategory($id);

    public function findCategoriesCountByParentId($parentId);

    public function makeNavCategories($code);

    public function findAllPublishedCategoriesByParentId($parentId);

    public function findCategoryTreeIds($parentId = 0, $isPublished = true);
}
