<?php

namespace Biz\Visualization\Service;

interface CoursePlanLearnDataDailyStatisticsService
{
    public function sumLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds);

    public function sumPureLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds);

    public function sumLearnedTimeByCourseId($courseId);

    public function sumLearnedTimeByCourseIds($courseIds);

    public function sumLearnedTimeGroupByUserId(array $conditions);

    public function sumLearnedTimeByConditions(array $conditions);
}
