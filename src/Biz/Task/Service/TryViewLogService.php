<?php

namespace Biz\Task\Service;

interface TryViewLogService
{
    public function createTryViewLog($tryViewLog);

    public function searchTryViewLogs($conditions, $sortBys, $start, $limit);

    public function countTryViewLogs($conditions);
}
