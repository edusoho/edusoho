<?php

namespace Activity\Service\Activity\Impl;

use Topxia\Service\Common\BaseService;
use Activity\Service\Activity\ActivityLearnLogService;

class ActivityLearnLogServiceImpl extends BaseService implements ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data)
    {
        $fields = array(
            'activityId'   => $activity['id'],
            'courseTaskId' => $data['task']['id'],
            'userId'       => $this->getCurrentUser()->getId(),
            'event'        => $eventName,
            'data'         => $data
        );
        return $this->getEventLogDao()->add($fields);
    }

    protected function getEventLogDao()
    {
        return $this->createDao('Activity:Activity.EventLogDao');
    }

}
