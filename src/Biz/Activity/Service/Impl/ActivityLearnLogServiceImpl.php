<?php

namespace Biz\Activity\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Dao\Impl\ActivityLearnLogDaoImpl;
use Biz\Task\Service\TaskResultService;
use AppBundle\Common\TimeMachine;

class ActivityLearnLogServiceImpl extends BaseService implements ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data)
    {
        if (!empty($data['lastTime'])) {
            $data['learnedTime'] = TimeMachine::time() - $data['lastTime'];
        }

        $fields = array(
            'activityId' => $activity['id'],
            'userId' => $this->getCurrentUser()->getId(),
            'event' => $eventName,
            'mediaType' => $activity['mediaType'],
            'learnedTime' => !empty($data['learnedTime']) ? $data['learnedTime'] : 0,
            'data' => $data,
            'createdTime' => time(),
        );

        //TODO 临时方案, 要考虑数据的准确性和扩展性
        if (!empty($data['watchTime'])) {
            $fields['learnedTime'] = $data['watchTime'];
        }
        if (!empty($data['task'])) {
            $fields['courseTaskId'] = $data['task']['id'];
        } elseif (!empty($data['taskId'])) {
            $fields['courseTaskId'] = $data['taskId'];
        } else {
            $fields['courseTaskId'] = 0;
        }

        return $this->getActivityLearnLogDao()->create($fields);
    }

    /**
     * @deprecated
     * @see #isActivityFinished($activityId)
     *
     * @param $activityId
     * @param $event
     *
     * @return array
     */
    public function getMyRecentFinishLogByActivityId($activityId)
    {
        $user = $this->getCurrentUser();

        return $this->getActivityLearnLogDao()->getRecentFinishedLogByActivityIdAndUserId($activityId, $user['id']);
    }

    /**
     * @deprecated
     * @see 尽量基于TaskResult统计
     * @see TaskResultService#calcLearnProcessByCourseIdAndUserId($courseId, $userId)
     *
     * @param $courseId
     * @param $userId
     *
     * @return array
     */
    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId)
    {
        $activities = $this->getActivityDao()->findByCourseId($courseId);
        $activityIds = ArrayToolkit::column($activities, 'id');
        $daysCount = $this->getActivityLearnLogDao()->countLearnedDaysByActivityIdsAndUserId($activityIds, $userId);
        $learnedTime = 0;
        $learnedTimePerDay = $daysCount > 0 ? $learnedTime / $daysCount : 0;

        return array($daysCount, $learnedTime, $learnedTimePerDay);
    }

    public function deleteLearnLogsByActivityId($activityId)
    {
        return $this->getActivityLearnLogDao()->deleteByActivityId($activityId);
    }

    public function getLastestLearnLogByActivityIdAndUserId($activityId, $userId)
    {
        return $this->getActivityLearnLogDao()->getLastestByActivityIdAndUserId($activityId, $userId);
    }

    public function sumLearnTimeGroupByUserId($conditions)
    {
        $results = $this->getActivityLearnLogDao()->sumLearnTimeGroupByUserId($conditions);
        $results = ArrayToolkit::index($results, 'userId');

        return $results;
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        return $this->getActivityLearnLogDao()->search($conditions, $orderBy, $start, $limit);
    }

    /**
     * @return ActivityLearnLogDaoImpl
     */
    protected function getActivityLearnLogDao()
    {
        return $this->createDao('Activity:ActivityLearnLogDao');
    }

    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }
}
