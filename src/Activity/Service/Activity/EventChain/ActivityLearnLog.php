<?php

namespace Activity\Service\Activity\EventChain;

use Topxia\Service\Common\ServiceKernel;

class ActivityLearnLog implements Event
{
    public function __construct($eventName)
    {
        $this->eventName = $eventName;
    }

    public function trigger($activity, $data)
    {
        $this->getActivityLearnLogService()->createLog($activity, $this->eventName, $data);
    }

    protected function getActivityLearnLogService()
    {
        ServiceKernel::instance()->createService('Activity:Activity.ActivityLearnLogService');
    }
}
