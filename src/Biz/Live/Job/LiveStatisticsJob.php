<?php

namespace Biz\Live\Job;

use Biz\Live\LiveStatisticsException;
use Biz\Live\Service\LiveStatisticsService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LiveStatisticsJob extends AbstractJob
{
    public function execute()
    {
        try {
            $this->getLiveStatisticsService()->updateCheckinStatistics($this->args['liveId']);
        } catch (LiveStatisticsException $e) {
            $this->getLogService()->warning('job', 'update_live_statistic_checkin', '获取直播点名信息失败:'.$e->getMessage());
        }

        try {
            $this->getLiveStatisticsService()->updateVisitorStatistics($this->args['liveId']);
        } catch (LiveStatisticsException $e) {
            $this->getLogService()->warning('job', 'update_live_statistic_checkin', '获取直播访问记录失败:'.$e->getMessage());
        }
    }

    /**
     * @return LiveStatisticsService
     */
    private function getLiveStatisticsService()
    {
        return $this->biz->service('Live:LiveStatisticsService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
