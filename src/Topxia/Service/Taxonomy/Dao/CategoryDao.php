<?php

namespace Topxia\Service\Taxonomy\Dao;

interface CategoryDao {

	function addCategory($category);

	function deleteCategory($id);

	function getCategory($id);

	function findCategoryByCode($code);

	function updateCategory($id, $category);

	function findCategoriesByParentId($parentId, $orderBy = null, $start, $limit);

	function findCategoriesCountByParentId($parentId);

	function findCategoriesByGroupId($groupId);

	function findCategoriesByIds(array $ids);

}