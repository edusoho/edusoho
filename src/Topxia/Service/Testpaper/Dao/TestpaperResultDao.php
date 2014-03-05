<?php

namespace Topxia\Service\Testpaper\Dao;

interface TestpaperResultDao
{
    public function getTestpaperResult($id);

    public function findTestpaperResultsByIds(array $ids);

    public function findTestpaperResultByTestpaperIdAndUserIdAndActive($testpaperId, $userId);

    public function findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, array $status, $userId);

    public function findTestPaperResultsByStatusAndTestIds ($ids, $status, $start, $limit);

    public function findTestPaperResultCountByStatusAndTestIds ($ids, $status);

    public function findTestPaperResultsByStatusAndTeacherIds ($ids, $status, $start, $limit);

    public function findTestPaperResultCountByStatusAndTeacherIds ($ids, $status);

    public function findTestPaperResultsByUserId ($id, $start, $limit);

    public function findTestPaperResultsCountByUserId ($id);

    public function searchTestpaperResults($conditions, $sort, $start, $limit);

    public function searchTestpaperResultsCount($conditions);

    public function addTestpaperResult($fields);

    public function updateTestpaperResult($id, $fields);

    public function updateTestpaperResultActive($testId,$userId);
}