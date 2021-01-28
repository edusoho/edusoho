<?php

namespace Biz\Activity\Listener;

use Biz\Activity\Service\ActivityLearnLogService;

class ActivityLearnLogListener extends Listener
{
    public function handle($activity, $data)
    {
        $event = $data['event'];
        $this->getActivityLearnLogService()->createLog($activity, $event, $data);
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:ActivityLearnLogService');
    }
}
