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
            'courseTaskId' => !empty($data['taskId']) ?: 0,
            'userId'       => $this->getCurrentUser()->getId(),
            'event'        => $eventName,
            'learnedTime'  => !empty($data['learnedTime']) ?: 0,
            'data'         => $data,
            'createdTime'  => time()
        );
        return $this->getActivityLearnLogDao()->create($fields);
    }

    public function sumLearnedTimeByActivityId($activityId)
    {
        $user = $this->getCurrentUser();
        return $this->getActivityLearnLogDao()->sumLearnedTimeByActivityIdAndUserId($activityId, $user['id']);
    }

    public function findMyLearnLogsByActivityIdAndEvent($activityId, $event)
    {
        $user = $this->getCurrentUser();
        return $this->getActivityLearnLogDao()->findByActivityIdAndUserIdAndEvent($activityId, $user['id'], $event);
    }

    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId)
    {
        $daysCount         = $this->getActivityLearnLogDao()->countLearnedDaysByCourseIdAndUserId($courseId, $userId);
        $learnedTime       = $this->getActivityLearnLogDao()->sumLearnedTimeByCourseIdAndUserId($courseId, $userId);
        $learnedTimePerDay = $daysCount > 0 ? $learnedTime / $daysCount : 0;

        return array($daysCount, $learnedTime, $learnedTimePerDay);
    }

    /**
     * @return ActivityLearnLogDaoImpl
     */
    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }

}
