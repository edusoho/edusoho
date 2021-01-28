<?php

namespace Biz\Live\Dao;

interface LiveStatisticsDao
{
    public function getByLiveIdAndType($liveId, $type);

    public function findByLiveIdsAndType(array $liveIds, $type);
}
