<?php

namespace Biz\Testpaper\Dao;

interface TestpaperItemDao
{
    public function findItemsByTestId($testpaperId);

    public function getItemsCountByTestId($testId);

    public function getItemsCountByParams(array $conditions, $groupBy = '');

    public function getItemsCountByTestIdAndParentId($testId, $parentId);

    public function getItemsCountByTestIdAndQuestionType($testId, $questionType);

    public function findItemByIds(array $ids);

    public function findTestpaperItemsByPIdAndLockedTestIds($pId, $testIds);

    public function deleteItemsByParentId($id);

    public function deleteItemsByTestpaperId($id);

    public function deleteItemByIds(array $ids);

    public function updateItemsMissScoreByPaperIds(array $ids, $missScore);

}
