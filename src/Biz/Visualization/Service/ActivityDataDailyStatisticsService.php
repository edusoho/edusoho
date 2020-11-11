<?php

namespace Biz\Visualization\Service;

interface ActivityDataDailyStatisticsService
{
    public function statisticsVideoDailyData($startTime, $endTime);

    public function statisticsLearnDailyData($dayTime);
}
