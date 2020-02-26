<?php

namespace Biz\Live\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LiveStatisticsJob extends AbstractJob
{
    public function execute()
    {
        $this->getLiveStatisticsService()->createLiveCheckinStatistics($this->args['liveId']);
        $this->getLiveStatisticsService()->createLiveHistoryStatistics($this->args['liveId']);
    }

    private function getLiveStatisticsService()
    {
        return $this->biz->service('Live:LiveStatisticsService');
    }
}
