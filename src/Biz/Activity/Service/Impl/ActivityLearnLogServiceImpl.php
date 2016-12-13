<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Dao\Impl\ActivityLearnLogDaoImpl;

class ActivityLearnLogServiceImpl extends BaseService implements ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data)
    {
        $fields = array(
            'activityId'   => $activity['id'],
            'courseTaskId' => !empty($data['taskId'])? :0,
            'userId'       => $this->getCurrentUser()->getId(),
            'event'        => $eventName,
            'learnedTime'  => !empty($data['learnedTime'])? :0,
            'data'         => $data,
            'createdTime'  => time()
        );
        return $this->getActivityLearnLogDao()->create($fields);
    }

    public function sumLearnedTimeByActivityId($activityId)
    {
        $user = $this->getCurrentUser();
        return $this->getActivityLearnLogDao()->sumLearnedTimeByActivityIdAndUserId($activityId,$user['id']);
    }

    public function findMyLearnLogsByActivityIdAndEvent($activityId, $event)
    {
        $user = $this->getCurrentUser();
        return $this->getActivityLearnLogDao()->findActivityLearnLogsByActivityIdAndUserIdAndEvent($activityId, $user['id'], $event);
    }

    /**
     * @return ActivityLearnLogDaoImpl
     */
    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }

}
