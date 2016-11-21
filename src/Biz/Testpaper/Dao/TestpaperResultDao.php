<?php

namespace Biz\Testpaper\Dao;

interface TestpaperResultDao
{
    public function getUserUnfinishResult($testId, $courseId, $lessonId, $type, $userId);

    public function getUserLatelyResultByTestId($userId, $testId, $courseId, $lessonId, $type);

    //old

    public function searchTestpapersScore($conditions);

}
