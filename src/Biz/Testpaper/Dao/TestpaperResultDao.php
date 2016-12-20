<?php

namespace Biz\Testpaper\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperResultDao extends GeneralDaoInterface
{
    public function getUserUnfinishResult($testId, $courseId, $lessonId, $type, $userId);

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $lessonId, $type);

    public function findPaperResultsStatusNumGroupByStatus($testId);

    public function searchTestpapersScore($conditions);

}
