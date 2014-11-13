<?php

namespace Topxia\Service\Taxonomy\Dao;

interface CategoryDao {

	public function addCategory($category);

	public function deleteCategory($id);

	public function getCategory($id);

	public function findCategoryByCode($code);

	public function findCategoriesByGroupIdAndParentId($groupId, $parentId);

	public function updateCategory($id, $category);

	public function findCategoriesByParentId($parentId, $orderBy = null, $start, $limit);
	
	public function findAllCategoriesByParentId($parentId);

	public function findCategoriesCountByParentId($parentId);

	public function findCategoriesByGroupId($groupId);

	public function findCategoriesByIds(array $ids);

	public function findAllCategories();

}