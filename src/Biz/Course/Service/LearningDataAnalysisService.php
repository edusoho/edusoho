<?php

namespace Biz\Course\Service;

interface LearningDataAnalysisService
{
    public function getUserLearningProgress($courseId, $userId);

    public function getUserLearningSchedule($courseId, $userId);
}