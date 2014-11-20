<?php
namespace Topxia\Service\Taxonomy;

interface CategoryService
{
    public function findCategoriesByGroupIdAndParentId($groupId, $parentId);

    public function findCategoriesByGroupCode($groupCode);

    public function findSubjectCategoriesByGradeId($gradeId);
    
    public function getMaterialCategoryByGradeIdAndSubjectId($gradeId,$subjectId);

    public function getCategory($id);

    public function getCategoryByCode($code);

    public function getCategoryTree($groupId);

    public function findCategories($groupId);
    
    public function findAllCategoriesByParentId($parentId);

    public function findCategoriesByParentIds(array $parentIds);

    public function findGroupRootCategories($groupCode);

    public function findCategoryChildrenIds($id);

    public function findCategoriesByIds(array $ids);

    public function findAllCategories();

    public function isCategoryCodeAvaliable($code, $exclude = null);

    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    public function deleteCategory($id);

    public function deleteCategorysByGroupId($groupId);
    
    public function getGroup($id);

    public function getGroupByCode($code);

    public function getGroups($start, $limit);

    public function findAllGroups();

    public function addGroup(array $group);

    public function deleteGroup($id);

    public function deleteAllCategories();

    public function deleteAllGroups();
}