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
     * @Log(level="info",module="category",action="create",message="添加栏目",targetType="article_category")
     */
    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(level="info",module="category",action="delete",message="删除栏目",targetType="article_category",format="{'before':{ 'className':'Article:CategoryService','funcName':'getCategory','param':['id']}}")
     */
    public function deleteCategory($id);

    public function findCategoriesCountByParentId($parentId);

    public function makeNavCategories($code);

    public function findAllPublishedCategoriesByParentId($parentId);

    public function findCategoryTreeIds($parentId = 0, $isPublished = true);
}
