<?php
namespace Topxia\Service\Article;

interface CategoryService
{

    public function getCategory($id);

    public function getCategoryByCode($code);

    public function getCategoryTree();

    public function findCategoryChildrenIds($id);

    public function findCategoriesByIds(array $ids);

    public function findAllCategories();

    public function isCategoryCodeAvaliable($code, $exclude = null);

    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    public function deleteCategory($id);

    public function findCategoriesCountByParentId($parentId);
    
}