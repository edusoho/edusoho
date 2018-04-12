<?php

namespace Codeages\Biz\Framework\Scheduler\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeleteFiredLogJob extends AbstractJob
{
    public function execute()
    {
        $keepDays = 15; //biz_scheduler_job_fired 只保留15天的日志
        $this->getSchedulerService()->deleteUnacquiredJobFired($keepDays);
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
