<?php

namespace Biz\Visualization\Job;

use Biz\Visualization\Service\ActivityDataDailyStatisticsService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class StatisticsCoursePlanLearnDailyDataJob extends AbstractJob
{
    public function execute()
    {
        $startTime = strtotime('yesterday');
        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanLearnDailyData($startTime);
    }

    /**
     * @return ActivityDataDailyStatisticsService
     */
    protected function getActivityDataDailyStatisticsService()
    {
        return $this->biz->service('Visualization:ActivityDataDailyStatisticsService');
    }
}
