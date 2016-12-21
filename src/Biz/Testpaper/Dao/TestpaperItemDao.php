<?php

namespace Biz\Testpaper\Dao;

interface TestpaperItemDao
{
    public function findItemsByIds(array $ids);

    public function findItemsByTestId($testpaperId);

    public function getItemsCountByParams(array $conditions, $groupBy = '');

    public function getItemsCountByTestId($testId);

    public function getItemsCountByTestIdAndParentId($testId, $parentId);

    public function getItemsCountByTestIdAndQuestionType($testId, $questionType);

    public function findTestpaperItemsByPIdAndLockedTestIds($pId, $testIds);

    public function deleteItemsByParentId($id);

    public function deleteItemsByTestpaperId($id);

    public function deleteItemByIds(array $ids);

    public function updateItemsMissScoreByPaperIds(array $ids, $missScore);

}
