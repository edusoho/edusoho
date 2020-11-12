<?php

namespace Biz\Visualization\Job;

use Biz\Visualization\Service\ActivityDataDailyStatisticsService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class StatisticsUserVideoDailyDataJob extends AbstractJob
{
    public function execute()
    {
        $startTime = strtotime('yesterday');
        $this->getActivityDataDailyStatisticsService()->statisticsUserVideoDailyData($startTime, $startTime + 86400);
    }

    /**
     * @return ActivityDataDailyStatisticsService
     */
    protected function getActivityDataDailyStatisticsService()
    {
        return $this->biz->service('Visualization:ActivityDataDailyStatisticsService');
    }
}
