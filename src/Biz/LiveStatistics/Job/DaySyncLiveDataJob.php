<?php

namespace Biz\LiveStatistics\Job;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Topxia\Service\Common\ServiceKernel;

class DaySyncLiveDataJob extends AbstractJob
{
    public function execute()
    {
        $activities = $this->getActivityService()->search(['mediaType' => 'live', 'startTime_GT' => strtotime(date('Y-m-d', strtotime('-1 day'))), 'startTime_LT' => strtotime(date('Y-m-d', time()))], [], 0, PHP_INT_MAX);
        foreach ($activities as $activity) {
            $live = $this->getLiveActivityService()->getLiveActivity($activity['mediaId']);
            if (empty($live)) {
                continue;
            }
            $startJob = [
                'name' => 'SyncLiveMemberDataJob'.$activity['id'].'_'.time(),
                'expression' => time() - 100,
                'class' => 'Biz\LiveStatistics\Job\SyncLiveMemberDataJob',
                'misfire_threshold' => 10 * 60,
                'args' => [
                    'activityId' => $activity['id'],
                    'start' => 0,
                    'syncLiveDetail' => 1,
                ],
            ];
            $this->getSchedulerService()->register($startJob);
        }
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return ServiceKernel::instance()->createService('Scheduler:SchedulerService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return ServiceKernel::instance()->createService('Activity:ActivityService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return ServiceKernel::instance()->createService('Activity:LiveActivityService');
    }
}
