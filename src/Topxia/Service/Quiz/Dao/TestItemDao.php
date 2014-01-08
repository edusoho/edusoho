<?php

namespace Topxia\Service\Quiz\Dao;

interface TestItemDao
{
    public function addItem($item);

    public function updateItem($id, $fields);

    public function deleteItem($id);

    public function deleteItemsByParentId($id);

    public function deleteItemsByTestPaperId($id);

    public function getItem($id);

    public function findItemByIds(array $ids);

    public function deleteItemByIds(array $ids);

    public function findItemsByTestPaperId($testPaperId);

    public function findItemsByTestPaperIdAndQuestionType($testPaperId, $field);

    public function getItemsCountByTestId($testId);
}