<?php

namespace Biz\SchedulerFacade\Service\Impl;

use Biz\SchedulerFacade\Service\SchedulerFacadeService;
use Codeages\Biz\Framework\Scheduler\Service\Impl\SchedulerServiceImpl;

class SchedulerFacadeServiceImpl extends SchedulerServiceImpl implements SchedulerFacadeService
{
    public function setNextFiredTime($jobId, $nextFiredTime)
    {
        $job = $this->getJob($jobId);

        if ($nextFiredTime > time() && !empty($job)) {
            return $this->getJobDao()->update($job['id'], array('next_fire_time' => $nextFiredTime));
        }
    }
}
