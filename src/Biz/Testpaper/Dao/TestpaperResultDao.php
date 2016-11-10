<?php

namespace Biz\Testpaper\Dao;

interface TestpaperResultDao
{
    public function getUserUnfinishResult($testId, $courseId, $lessonId, $type, $userId);

    public function findTestpaperResultByTestpaperIdAndUserIdAndActive($testpaperId, $userId);

    public function findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, array $status, $userId);

    public function findTestPaperResultsByStatusAndTestIds($ids, $status, $start, $limit);

    public function findTestPaperResultCountByStatusAndTestIds($ids, $status);

    public function findTestPaperResultsByStatusAndTeacherIds($ids, $status, $start, $limit);

    public function findTestPaperResultCountByStatusAndTeacherIds($ids, $status);

    public function findTestPaperResultsByUserId($id, $start, $limit);

    public function findTestPaperResultsCountByUserId($id);

    public function searchTestpapersScore($conditions);

    public function updateTestpaperResultActive($testId, $userId);

    public function updateTestResultsByTarget($target, $fields);

    public function deleteTestpaperResultByTestpaperId($testpaperId);

    public function deleteTestpaperResultByTestpaperIdAndStatus($testpaperId, $status);
}
