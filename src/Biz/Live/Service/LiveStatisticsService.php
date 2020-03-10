<?php

namespace Biz\Live\Service;

interface LiveStatisticsService
{
    const STATISTICS_TYPE_CHECKIN = 'checkin';
    const STATISTICS_TYPE_VISITOR = 'visitor';

    public function createLiveCheckinStatistics($liveId);

    public function createLiveVisitorStatistics($liveId);

    public function updateCheckinStatistics($liveId);

    public function updateVisitorStatistics($liveId);

    public function getCheckinStatisticsByLiveId($liveId);

    public function getVisitorStatisticsByLiveId($liveId);

    public function findCheckinStatisticsByLiveIds($liveIds);

    public function findVisitorStatisticsByLiveIds($liveIds);
}
