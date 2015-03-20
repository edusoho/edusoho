<?php

namespace Topxia\Service\Testpaper\Dao;

interface TestpaperItemResultDao
{
    public function addItemAnswers ($testPaperResultId, $answers, $testPaperId, $userId);
	
    public function addItemResults ($testPaperResultId, $answers, $testId, $userId);

	public function updateItemAnswers ($testPaperResultId, $answers);

    public function updateItemResults ($answers, $testPaperResultId);

    public function updateItemEssays ($answers, $testPaperResultId);

	public function findTestResultsByItemIdAndTestId ($questionIds, $testPaperResultId);

    public function findTestResultsByTestPaperResultId ($testPaperResultId);

    public function findRightItemCountByTestPaperResultId ($testPaperResultId);

    public function findWrongResultByUserId($id, $start, $limit);

    public function findWrongResultCountByUserId ($id);
}