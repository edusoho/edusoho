<?php

namespace Topxia\Service\Quiz\Dao;

interface TestItemDao
{
    public function addItem($item);

    public function addItems(array $items);     //`testId`,`seq`,`questionId`,`questionType`,`score`
    
    public function updateItem($id, $fields);

    public function deleteItem($id);

    public function deleteItemsByParentId($id);

    public function getItem($id);

    public function findItemByIds(array $ids);

    public function deleteItemByIds(array $ids);

    public function searchItemCount($conditions);

    public function searchItem($conditions, $orderBy, $start, $limit);

    public function getItemsCountByTestId($testId);
}