<?php

namespace Biz\Visualization\Service;

interface ActivityDataDailyStatisticsService
{
    public function statisticsPageStayDailyData($startTime, $endTime);

    public function statisticsVideoDailyData($startTime, $endTime);

    public function statisticsLearnDailyData($dayTime);
}
