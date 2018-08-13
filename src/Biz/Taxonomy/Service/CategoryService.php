<?php

namespace Biz\Taxonomy\Service;

use Biz\System\Annotation\Log;

interface CategoryService
{
    public function findCategoriesByGroupIdAndParentId($groupId, $parentId);

    public function getCategory($id);

    public function getCategoryByCode($code);

    public function getCategoryTree($groupId);

    public function getCategoryStructureTree($groupId);

    public function findCategories($groupId);

    public function findAllCategoriesByParentId($parentId);

    public function findGroupRootCategories($groupCode);

    public function findCategoryChildrenIds($id);

    public function findCategoriesByIds(array $ids);

    public function findAllCategories();

    public function makeNavCategories($code, $groupCode);

    public function findCategoryBreadcrumbs($categoryId);

    public function isCategoryCodeAvailable($code, $exclude = null);

    /**
     * @param $category
     *
     * @return mixed
     * @Log(level="info",module="category",action="create",message="添加分类",targetType="category")
     */
    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    public function deleteCategory($id);

    public function getGroup($id);

    public function getGroupByCode($code);

    public function getGroups($start, $limit);

    public function findAllGroups();

    public function addGroup(array $group);

    public function deleteGroup($id);

    public function sortCategories($ids);
}
