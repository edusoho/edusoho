<?php

namespace Biz\Task\Service;

interface TryViewLogService
{
    public function createTryViewLog($tryViewLog);

    public function searchTryViewLogs($conditions, $sortBys, $start, $limit);

    public function countTryViewLogs($conditions);

    public function searchLogCountsByCourseIdAndTimeRange($courseId, $timeRange = array(), $format = '%Y-%m-%d');
}
