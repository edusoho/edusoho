<?php

namespace Biz\Live\Dao;

interface LiveStatisticsDao
{
    public function findByLiveIdsAndType(array $liveIds, $type);
}
