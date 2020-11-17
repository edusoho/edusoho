<?php

namespace Biz\Visualization\Service;

interface CoursePlanLearnDataDailyStatisticsService
{
    public function sumLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds);
}
