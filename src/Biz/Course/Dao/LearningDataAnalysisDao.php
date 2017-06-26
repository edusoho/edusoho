<?php

namespace Biz\Course\Dao;

interface LearningDataAnalysisDao
{
    public function countStatisticDataByCourseIdsAndUserId($courseIds, $userId);
}
