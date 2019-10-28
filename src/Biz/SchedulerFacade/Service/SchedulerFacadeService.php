<?php

namespace Biz\SchedulerFacade\Service;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

interface SchedulerFacadeService extends SchedulerService
{
    public function setNextFiredTime($jobId, $nextFiredTime);
}
