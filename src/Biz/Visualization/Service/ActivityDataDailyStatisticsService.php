<?php

namespace Biz\Visualization\Service;

interface ActivityDataDailyStatisticsService
{
    public function statisticsPageStayDailyData($startTime, $endTime);

    public function statisticsVideoDailyData($startTime, $endTime);

    public function statisticsLearnDailyData($dayTime);

    public function statisticsUserLearnDailyData($dayTime);

    public function statisticsCoursePlanLearnDailyData($dayTime);

    public function statisticsUserStayDailyData($startTime, $endTime);

    public function statisticsUserVideoDailyData($startTime, $endTime);

    public function statisticsCoursePlanStayDailyData($startTime, $endTime);

    public function statisticsCoursePlanVideoDailyData($startTime, $endTime);

    public function sumTaskResultTime($dayTime);

    public function findUserLearnRecords($conditions);

    public function getVideoEffectiveTimeStatisticsSetting();

    public function getDailyLearnData($userId, $startTime, $endTime);
}
