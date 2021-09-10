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
        if ('closeSecondJob' === $jobType) {
            $this->getLiveActivityService()->closeLive($liveId, time());

            return;
        }
        $confirmStatus = $this->getLiveService()->confirmLiveStatus([$liveId]);
        $status = $confirmStatus[$liveId]['status'];
        if ('start' === $status) {
            $startTime = $confirmStatus[$liveId]['startTime'] ?: time();
            $this->getLiveActivityService()->startLive($liveId, $startTime);
        }
        if ('close' === $status) {
            $closeTime = $confirmStatus[$liveId]['endTime'] ?: time();
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
