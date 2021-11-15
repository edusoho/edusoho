<?php

namespace Biz\LiveStatistics\Service;

interface LiveCloudStatisticsService
{
    public function searchCourseMemberLiveData($conditions, $start, $limit);

    public function countLiveMembersByLiveId($liveId);

    public function sumWatchDurationByLiveId($liveId);

    public function getLiveData($task);

    public function processEsLiveMemberData($activity, $memberData);

    public function processGeneralLiveMemberData($activity, $memberData);
}
