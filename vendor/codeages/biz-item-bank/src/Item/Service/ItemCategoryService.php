<?php

namespace Codeages\Biz\ItemBank\Item\Service;

interface ItemCategoryService
{
    public function createItemCategory($category);

    public function createItemCategories($bankId, $parentId, $names);

    public function updateItemCategory($id, $category);

    public function getItemCategory($id);

    public function deleteItemCategory($id);

    public function findItemCategoriesByIds($ids);

    public function findItemCategoriesByBankId($bankId);

    public function getItemCategoryTree($bankId);

    public function getItemCategoryMap($bankId);

    public function findCategoryChildrenIds($id);

    public function findMultiCategoryChildrenIds($bankId, $ids);

    public function getItemCategoryTreeList($bankId);

    public function updateItemNumAndQuestionNum($id);

    public function buildItemNumAndQuestionNumBybankId($bankId);

    public function sortItemCategories($ids);

    public function buildCategoryTreeList($categories, $parentId);

    public function buildCategoryTree($categories);

    public function buildCategoryMap($categories);
}
