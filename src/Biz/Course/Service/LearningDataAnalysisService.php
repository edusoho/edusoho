<?php

namespace Biz\Course\Service;

interface LearningDataAnalysisService
{
    public function getUserLearningProgress($courseId, $userId);

    public function fillCourseProgress($members);

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

    /**
     * 用户对多个课程必修课的总进度
     *
     * @param $courseIds
     * @param $userId
     *
     * @return mixed
     */
    public function getUserLearningCompulsoryProgressByCourseIds($courseIds, $userId);

    public function getUserLearningSchedule($courseId, $userId);
}
