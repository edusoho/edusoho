<?php

namespace Biz\Activity\Service\Impl;


use Biz\Activity\Dao\Impl\ActivityLearnLogDaoImpl;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\BaseService;

class ActivityLearnLogServiceImpl extends BaseService  implements ActivityLearnLogService
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
        return $this->getActivityLearnLogDao()->create($fields);
    }

    /**
     * @return ActivityLearnLogDaoImpl
     */
    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }

}
