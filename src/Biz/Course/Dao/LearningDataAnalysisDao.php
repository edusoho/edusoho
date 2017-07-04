<?php

namespace Biz\Course\Dao;

interface LearningDataAnalysisDao
{
    public function getStatisticDataByCourseIdsAndUserId($courseIds, $userId);

    public function batchRefreshUserLearningData($courseId, $userIds);
}
