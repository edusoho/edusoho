<?php

namespace Biz\Classroom\Service;

interface LearningDataAnalysisService
{
    public function getUserLearningProgress($classroomId, $userId);
}
