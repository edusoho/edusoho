<?php

namespace Biz\Course\Dao;

interface LearningDataAnalysisDao
{
    public function sumStatisticDataByCourseIdsAndUserId($courseIds, $userId);

    public function batchRefreshUserLearningData($courseId, $userIds);
}
