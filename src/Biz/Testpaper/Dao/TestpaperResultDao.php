<?php

namespace Biz\Testpaper\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperResultDao extends GeneralDaoInterface
{
    public function getUserUnfinishResult($testId, $courseId, $activityId, $type, $userId);

    public function getUserFinishedResult($testId, $courseId, $activityId, $type, $userId);

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $lessonId, $type);

    public function findPaperResultsStatusNumGroupByStatus($testId, $activityId);

    public function sumScoreByParams($conditions);

    public function findByIds($ids);
}
