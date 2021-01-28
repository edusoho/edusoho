<?php

namespace Codeages\Biz\Framework\Scheduler\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class MarkExecutingTimeoutJob extends AbstractJob
{
    public function execute()
    {
        $this->getSchedulerService()->markTimeoutJobs();
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
