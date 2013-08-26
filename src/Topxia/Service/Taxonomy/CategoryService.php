<?php
namespace Topxia\Service\Taxonomy;

interface CategoryService
{

    /**
     * category
     */
    public function createCategory(array $category);

    public function getCategory($id);

    public function getCategoryByCode($code);

    public function isCategoryCodeAvaliable($code, $exclude = null);

    public function findCategoriesByIds(array $ids);

    public function updateCategory($id, array $fields);

    /**
     * 获得某个Group下的所有分类
     *
     * $depth 为０，则$depth为其Group所属的默认设置的值。
     */
    public function getCategories($groupId);

    public function getCategoryTree($groupId);

    public function findCategoryChildrenIds($id);

    public function deleteCategory($id);

    /**
     * group
     */
    public function getGroup($id);

    public function getGroupByCode($code);

    public function getGroups($start, $limit);

    /**
    *分类的分组系统初始化时初始化好，此方法仅仅给单元测试使用
    */
    public function addGroup(array $group);

}