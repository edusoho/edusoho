<?php

namespace Biz\Visualization\Service;

interface CoursePlanLearnDataDailyStatisticsService
{
    public function sumLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds);

    public function sumPureLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds);

    public function sumLearnedTimeByCourseId($courseId);
}
