<?php

namespace Topxia\Service\Article\Dao;

interface CategoryDao 
{

	public function addCategory($category);

	public function deleteCategory($id);

	public function getCategory($id);

	public function findCategoryByCode($code);
	
	public function getCategoryByParentId($parentId);

	public function findAllCategories();

	public function updateCategory($id, $category);

	public function findCategoriesByParentId($parentId, $orderBy = null, $start, $limit);

	public function findCategoriesCountByParentId($parentId);

	public function findCategoriesByIds(array $ids);

}
	