<?php

namespace Biz\Article\Service;

use Biz\System\Annotation\Log;

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

    /**
     * @param $category
     *
     * @return mixed
     * @Log(module="category",action="create")
     */
    public function createCategory(array $category);

    /**
     * @param $id
     * @param array $fields
     *
     * @return mixed
     * @Log(module="category",action="update")
     */
    public function updateCategory($id, array $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="category",action="delete")
     */
    public function deleteCategory($id);

    public function findCategoriesCountByParentId($parentId);

    public function makeNavCategories($code);

    public function findAllPublishedCategoriesByParentId($parentId);

    public function findCategoryTreeIds($parentId = 0, $isPublished = true);
}
