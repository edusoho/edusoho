<?php

namespace Biz\Live\Job;

use Biz\Live\Service\LiveStatisticsService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LiveStatisticsJob extends AbstractJob
{
    public function execute()
    {
        $this->getLiveStatisticsService()->updateCheckinStatistics($this->args['liveId']);
        $this->getLiveStatisticsService()->updateVisitorStatistics($this->args['liveId']);
    }

    /**
     * @return LiveStatisticsService
     */
    private function getLiveStatisticsService()
    {
        return $this->biz->service('Live:LiveStatisticsService');
    }
}
