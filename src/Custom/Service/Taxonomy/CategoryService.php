<?php
namespace Custom\Service\Taxonomy;

use Topxia\Service\Taxonomy\CategoryService as BaseCategoryService;

interface CategoryService extends BaseCategoryService
{
	  public function updateCategoryIsSearch($id, $isSearch);
}