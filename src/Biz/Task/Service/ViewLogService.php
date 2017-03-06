<?php

namespace Biz\Task\Service;

interface ViewLogService
{
    public function createViewLog($viewLog);

    public function searchViewLogs($conditions, $sortBys, $start, $limit);

    public function countViewLogs($conditions);

    public function searchViewLogsGroupByTime($conditions, $startTime, $endTime);
}
