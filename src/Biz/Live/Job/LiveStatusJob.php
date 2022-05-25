<?php

namespace Biz\Live\Job;

use Biz\Activity\Service\LiveActivityService;
use Biz\AppLoggerConstant;
use Biz\Live\Service\LiveService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LiveStatusJob extends AbstractJob
{
    public function execute()
    {
        $liveId = $this->args['liveId'];
        $jobType = $this->args['jobType'];
        $liveActivity = $this->getLiveActivityService()->getByLiveId($liveId);
        $canExecute = $this->getLiveService()->canExecuteLiveStatusJob($liveActivity['progressStatus'], $jobType);
        if (!$canExecute) {
            $this->getLogService()->error(AppLoggerConstant::LIVE, 'update_live_status', "不能执行更新 {$liveId} 从 {$liveActivity['progressStatus']} 至 {$jobType}");
            return;
        }
        if ('closeSecondJob' === $jobType) {
            $this->getLiveActivityService()->closeLive($liveId, time());

            return;
        }
        $confirmStatus = $this->getLiveService()->confirmLiveStatus($liveId);
        $status = $confirmStatus['status'] ?: 'unknown';
        if ('living' === $status) {
            $startTime = $confirmStatus['liveStartTime'] ?: time();
            $this->getLiveActivityService()->startLive($liveId, $startTime);
        }
        if ('finished' === $status) {
            $closeTime = $confirmStatus['liveEndTime'] ?: time();
            $this->getLiveActivityService()->closeLive($liveId, $closeTime);
        }
    }

    /**
     * @return LiveService
     */
    protected function getLiveService()
    {
        return $this->biz->service('Live:LiveService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->biz->service('Activity:LiveActivityService');
    }
}
