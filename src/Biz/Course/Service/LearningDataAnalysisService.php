<?php

namespace Biz\Course\Service;

interface LearningDataAnalysisService
{
    public function getUserLearningProgress($courseId, $userId);

    public function makeProgress($learnedNum, $total);

    /**
     * 用户对多个课程的总进度
     *
     * @param $courseIds
     * @param $userId
     *
     * @return mixed
     */
    public function getUserLearningProgressByCourseIds($courseIds, $userId);

    public function getUserLearningSchedule($courseId, $userId);
}
