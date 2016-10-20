<?php

namespace Biz\Activity\Event;


use Biz\Activity\Service\ActivityLearnLogService;

class ActivityLearnLogEvent extends Event
{
    public function trigger()
    {
        $this->getActivityLearnLogService()->createLog($this->getSubject(), $this->getName(), $this->getArguments());
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:ActivityLearnLogService');
    }
}
