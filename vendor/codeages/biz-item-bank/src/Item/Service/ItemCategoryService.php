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

    public function findCategoryChildrenIds($id);

    public function getItemCategoryTreeList($bankId);
}
