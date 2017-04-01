<?php

namespace Biz\Testpaper\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperItemResultDao extends GeneralDaoInterface
{
    public function findItemResultsByResultId($resultId, $type);

    public function addItemAnswers($testPaperResultId, $answers, $testPaperId, $userId);

    public function updateItemAnswers($testPaperResultId, $answers);

    public function updateItemResults($answers, $testPaperResultId);

    public function updateItemEssays($answers, $testPaperResultId);

    public function findTestResultsByItemIdAndTestId($questionIds, $testPaperResultId);

    public function findRightItemCountByTestPaperResultId($testPaperResultId);

    public function findWrongResultByUserId($id, $start, $limit);

    public function findWrongResultCountByUserId($id);

    public function deleteTestpaperItemResultByTestpaperId($testpaperId);
}
