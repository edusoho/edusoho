<?php

namespace Biz\Course\Service;

interface LearningDataAnalysisService
{
    public function getUserLearningProgress($courseId, $userId, $isRealTime = true);

    public function getUserLearningSchedule($courseId, $userId);
}
