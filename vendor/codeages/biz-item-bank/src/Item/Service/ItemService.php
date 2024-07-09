<?php

namespace Codeages\Biz\ItemBank\Item\Service;

interface ItemService
{
    public function createItem($item, $isBatch = false);

    public function importItems($items, $bankId);

    public function readWordFile($wordPath, $resourcePath = '');

    public function parseItems($text);

    public function updateItem($id, $item);

    public function getItem($id);

    public function getItemIncludeDeleted($id);

    public function getItemWithQuestions($id, $withAnswer = false);

    public function findItemsByIds($ids, $withQuestions = false);

    public function findItemsByIdsIncludeDeleted($ids, $withQuestions = false);

    public function searchItems($conditions, $orderBys, $start, $limit, $columns = []);

    public function searchItemsIncludeDeleted($conditions, $orderBys, $start, $limit, $columns = []);

    public function countItems($conditions);

    public function getItemCountGroupByTypes($conditions);

    public function getItemCountGroupByDifficulty($conditions);

    public function countItemGroupByCategoryIdAndType($conditions);

    public function findItemsByCategoryIds($categoryIds);

    public function deleteItem($id, $isBatch = false);

    public function deleteItems($ids);

    public function updateItemsCategoryId($ids, $categoryId);

    public function review($itemResponses);

    public function exportItems($bankId, $conditions, $path, $imgRootDir);

    public function findQuestionsByQuestionIds($questionIds);

    public function findQuestionsByQuestionIdsIncludeDeleted($questionIds);

    public function countQuestionsByBankId($bankId);

    public function countQuestionsByCategoryId($categoryId);

    public function searchQuestions($conditions, $orderBys, $start, $limit, $columns = []);

    public function getQuestionIncludeDeleted($questionId);

    public function getQuestion($questionId);

    public function countItemTypesNum($items);

    public function findDuplicatedMaterialIds($itemBankId, $items);

    public function isMaterialDuplicative($itemBankId, $material, $items = [], $itemId = 0);

    public function findDuplicatedMaterials($bankId, $categoryId = 0);

    public function findDuplicatedMaterialItems($bankId, $categoryId, $material);
}
