<?php

namespace Biz\Live\Service;

interface LiveStatisticsService
{
    public function createLiveCheckinStatistics($liveId);

    public function findCheckinStatisticsByLiveIds($liveIds);

    public function findByLiveIdsAndType(array $liveIds, $type);
}
