<?php

namespace Biz\Activity\Event;


class ActivityLearnLogEvent extends Event
{
    public function trigger($activity, $data)
    {
        $this->getActivityLearnLogService()->createLog($activity, $this->eventName, $data);
    }

    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:Activity.ActivityLearnLogService');
    }
}
