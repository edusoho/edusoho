<?php

namespace Biz\Live\Job;

use Biz\Activity\Service\LiveActivityService;
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
            return;
        }
        $confirmStatus = $this->getLiveService()->confirmLiveStatus($liveId);
        $status = !empty($confirmStatus['data']) ? $confirmStatus['data']['status'] : 'unknown';
        if ('living' === $status) {
            $startTime = $confirmStatus['liveStartTime'] ?: 0;
            $this->getLiveActivityService()->startLive($liveId, $startTime);
        }
        if ('finished' === $status || (false !== strpos($jobType, 'close') && 'living' !== $status)) {
            $closeTime = $confirmStatus['liveEndTime'] ?: 0;
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
