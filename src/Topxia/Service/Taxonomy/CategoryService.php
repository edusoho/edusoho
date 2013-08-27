<?php
namespace Topxia\Service\Taxonomy;

interface CategoryService
{

    public function getCategory($id);

    public function getCategoryByCode($code);

    public function getCategoryTree($groupId);

    public function findCategories($groupId);

    public function findCategoryChildrenIds($id);

    public function findCategoriesByIds(array $ids);

    public function isCategoryCodeAvaliable($code, $exclude = null);

    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    public function deleteCategory($id);

    public function getGroup($id);

    public function getGroupByCode($code);

    public function getGroups($start, $limit);

    public function addGroup(array $group);
}