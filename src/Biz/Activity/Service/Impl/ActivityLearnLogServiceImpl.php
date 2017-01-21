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
            'activityId'  => $activity['id'],
            'userId'      => $this->getCurrentUser()->getId(),
            'event'       => $eventName,
            'learnedTime' => !empty($data['learnedTime']) ?: 0,
            'watchTime'   => !empty($data['watchTime']) ? $data['watchTime'] : 0,
            'data'        => $data,
            'createdTime' => time()
        );
        if (!empty($data['task'])) {
            $fields['courseTaskId'] = $data['task']['id'];
        } elseif (!empty($data['taskId'])) {
            $fields['courseTaskId'] = $data['taskId'];
        } else {
            $fields['courseTaskId'] = 0;
        }
        return $this->getActivityLearnLogDao()->create($fields);
    }

    public function sumLearnedTimeByActivityId($activityId)
    {
        return $this->getActivityLearnLogDao()->sumLearnedTimeByActivityId($activityId);
    }

    public function sumWatchTimeByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getActivityLearnLogDao()->sumWatchTimeByActivityIdAndUserId($activityId, $userId);
    }


    public function sumMyLearnedTimeByActivityId($activityId)
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

    public function sumLearnTime($conditions)
    {
        return $this->getActivityLearnLogDao()->sumLearnTime($conditions);
    }

    public function sumWatchTime($conditions)
    {
        //1. 视为所有的任务均统计观看时长，
        //2. 对于无法统计观看时长的，不会尤其learnTime，所以暂时统计learnTime
        return $this->sumLearnTime($conditions);
    }

    /**
     * @return ActivityLearnLogDaoImpl
     */
    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }
}
