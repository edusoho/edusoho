<?php

namespace Codeages\Biz\ItemBank\Item\Service;

interface ItemService
{
    public function createItem($item);

    public function importItems($items, $bankId);

    public function readWordFile($wordPath, $resourcePath = '');

    public function parseItems($text);

    public function updateItem($id, $item);

    public function getItem($id);

    public function getItemWithQuestions($id, $withAnswer = false);

    public function findItemsByIds($ids, $withQuestions = false);

    public function searchItems($conditions, $orderBys, $start, $limit, $columns = []);

    public function countItems($conditions);

    public function getItemCountGroupByTypes($conditions);

    public function findItemsByCategoryIds($categoryIds);

    public function deleteItem($id);

    public function deleteItems($ids);

    public function updateItemsCategoryId($ids, $categoryId);

    public function findQuestionsByItemIds($itemIds);

    public function review($itemResponses);

    public function exportItems($bankId, $conditions, $path, $imgRootDir);

    public function findQuestionsByQuestionIds($questionIds);
}
