<?php

namespace Biz\Testpaper\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperItemResultDao extends GeneralDaoInterface
{
    public function findItemResultsByResultId($resultId, $type);

    public function addItemAnswers($testPaperResultId, $answers, $testPaperId, $userId);

    public function updateItemAnswers($testPaperResultId, $answers);

    public function updateItemResults($testPaperResultId, $answers);

    public function updateItemEssays($testPaperResultId, $answers);

    public function findTestResultsByItemIdAndTestId($questionIds, $testPaperResultId);

    public function countRightItemByTestPaperResultId($testPaperResultId);

    public function findWrongResultByUserId($id, $start, $limit);

    public function countWrongResultByUserId($id);

    public function deleteTestpaperItemResultByTestpaperId($testpaperId);
}
