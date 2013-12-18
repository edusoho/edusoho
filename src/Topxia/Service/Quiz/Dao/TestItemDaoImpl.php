<?php

namespace Topxia\Service\Quiz\Dao;

interface TestItemDao
{
    public function addItem($questions);

    public function updateItem($id, $fields);

    public function deleteItem($id);

    public function deleteItemsByParentId($id);

    public function getItem($id);

    public function findItemByIds(array $ids);

    public function deleteItemByIds(array $ids);

    public function searchItemCount($conditions);

    public function searchItem($conditions, $orderBy, $start, $limit);
}