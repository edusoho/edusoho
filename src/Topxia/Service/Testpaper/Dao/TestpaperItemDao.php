<?php

namespace Topxia\Service\Testpaper\Dao;

interface TestpaperItemDao
{
    public function getItem($id);

    public function addItem($item);

    public function updateItem($id, $fields);

    public function deleteItem($id);

    public function deleteItemsByParentId($id);

    public function deleteItemsByTestpaperId($id);

    public function deleteTestpaperItemByPId($pId);

    public function deleteTestpaperItemByTestId($testId);

    public function findItemByIds(array $ids);

    public function findItemsByTestPaperId($testPaperId);

    public function getItemsCountByTestId($testId);

    public function getItemsCountByTestIdAndParentId($testId, $parentId);

    public function getItemsCountByTestIdAndQuestionType($testId, $questionType);

    public function deleteItemByIds(array $ids);

    public function updateItemsMissScoreByPaperIds(array $ids, $missScore);

    public function updateTestpaperItemByPId($pId, $item);

    public function updateTestpaperItemByTestId($testId, $fields);
}