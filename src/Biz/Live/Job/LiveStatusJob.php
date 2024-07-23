<?php

namespace Biz\Live\Job;

use Biz\Activity\Service\LiveActivityService;
use Biz\Live\Constant\LiveStatus;
use Biz\Live\Service\LiveService;
use Biz\System\Constant\LogModule;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LiveStatusJob extends AbstractJob
{
    public function execute()
    {
        $liveId = $this->args['liveId'];
        $jobType = $this->args['jobType'];
        $liveActivity = $this->getLiveActivityService()->getByLiveId($liveId);
        $canExecute = $this->canExecute($liveActivity['progressStatus'], $jobType);
        if (!$canExecute) {
            return;
        }
        $confirmStatus = $this->getLiveService()->confirmLiveStatus($liveId);
        $this->getLogService()->info(LogModule::LIVE, 'confirm_live_status', '获取直播真实状态', $confirmStatus);
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

    public function canExecute($liveStatus, $jobType)
    {
        if ('startJob' === $jobType) {
            return LiveStatus::CREATED === $liveStatus;
        }

        return LiveStatus::CLOSED !== $liveStatus;
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

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
