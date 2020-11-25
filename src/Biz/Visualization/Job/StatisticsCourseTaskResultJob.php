<?php

namespace Biz\Visualization\Job;

use Biz\Visualization\Service\ActivityDataDailyStatisticsService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class StatisticsCourseTaskResultJob extends AbstractJob
{
    public function execute()
    {
        $dayTime = strtotime('yesterday');
        $this->getActivityDataDailyStatisticsService()->sumTaskResultTime($dayTime);
    }

    /**
     * @return ActivityDataDailyStatisticsService
     */
    protected function getActivityDataDailyStatisticsService()
    {
        return $this->biz->service('Visualization:ActivityDataDailyStatisticsService');
    }
}
