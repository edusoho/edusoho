<?php

namespace Biz\Activity\Event;


use Biz\Activity\Service\ActivityLearnLogService;

class ActivityLearnLogEvent extends Event
{
    public function trigger($activity, $data)
    {
        $this->getActivityLearnLogService()->createLog($activity, $this->getName(), $data);
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:ActivityLearnLogService');
    }
}
