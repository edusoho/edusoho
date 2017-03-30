<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Dao\Impl\ActivityLearnLogDaoImpl;

class ActivityLearnLogServiceImpl extends BaseService implements ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data)
    {
        if (!empty($data['lastTime'])) {
            $data['learnedTime'] = time() - $data['lastTime'];
        }

        $fields = array(
            'activityId' => $activity['id'],
            'userId' => $this->getCurrentUser()->getId(),
            'event' => $eventName,
            'watchTime' => !empty($data['watchTime']) ? $data['watchTime'] : 0,
            'learnedTime' => !empty($data['learnedTime']) ? $data['learnedTime'] : 0,
            'data' => $data,
            'createdTime' => time(),
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
        $daysCount = $this->getActivityLearnLogDao()->countLearnedDaysByCourseIdAndUserId($courseId, $userId);
        $learnedTime = $this->getActivityLearnLogDao()->sumLearnedTimeByCourseIdAndUserId($courseId, $userId);
        $learnedTimePerDay = $daysCount > 0 ? $learnedTime / $daysCount : 0;

        return array($daysCount, $learnedTime, $learnedTimePerDay);
    }

    public function sumLearnTime($conditions)
    {
        $result = $this->getActivityLearnLogDao()->sumLearnTime($conditions);

        return (int) ($result / 60);
    }

    public function sumWatchTime($conditions)
    {
        //1. 视为所有的任务均统计观看时长，
        //2. 对于无法统计观看时长的，不会有learnTime，所以暂时统计learnTime
        return $this->sumLearnTime($conditions);
    }

    public function deleteLearnLogsByActivityId($activityId)
    {
        return $this->getActivityLearnLogDao()->deleteByActivityId($activityId);
    }

    /**
     * @return ActivityLearnLogDaoImpl
     */
    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }
}
