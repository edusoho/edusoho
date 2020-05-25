<?php

namespace Biz\Testpaper\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperItemDao extends GeneralDaoInterface
{
    public function findItemsByIds(array $ids);

    public function findItemsByTestId($testpaperId, $type);

    public function findItemsByTestIds($testpaperIds);

    public function getItemsCountByParams(array $conditions, $groupBy = '');

    public function getItemsCountByTestId($testId);

    public function getItemsCountByTestIdAndParentId($testId, $parentId);

    public function getItemsCountByTestIdAndQuestionType($testId, $questionType);

    public function findTestpaperItemsByCopyIdAndLockedTestIds($copyId, $testIds);

    public function deleteItemsByParentId($id);

    public function deleteItemsByTestpaperId($id);

    public function deleteItemByIds(array $ids);

    public function changeItemsMissScoreByPaperIds(array $ids, $missScore);
}
