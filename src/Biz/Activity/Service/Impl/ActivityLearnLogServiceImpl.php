<?php

namespace Biz\Activity\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Activity\Dao\ActivityLearnLogDao;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\BaseService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class ActivityLearnLogServiceImpl extends BaseService implements ActivityLearnLogService
{
    public function createLog($activity, $eventName, $data)
    {
        if (!empty($data['lastTime'])) {
            $data['learnedTime'] = TimeMachine::time() - $data['lastTime'];
        }

        $fields = [
            'activityId' => $activity['id'],
            'userId' => $this->getCurrentUser()->getId(),
            'event' => $eventName,
            'mediaType' => $activity['mediaType'],
            'learnedTime' => !empty($data['learnedTime']) ? $data['learnedTime'] : 0,
            'data' => $data,
            'createdTime' => time(),
        ];

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
     * @param $activityId
     * @param $event
     *
     * @return array
     *
     * @see #isActivityFinished($activityId)
     * @deprecated
     */
    public function getMyRecentFinishLogByActivityId($activityId)
    {
        $user = $this->getCurrentUser();

        return $this->getActivityLearnLogDao()->getRecentFinishedLogByActivityIdAndUserId($activityId, $user['id']);
    }

    /**
     * @param $courseId
     * @param $userId
     *
     * @return array
     *
     * @deprecated
     * @see 尽量基于TaskResult统计
     * @see TaskResultService#calcLearnProcessByCourseIdAndUserId($courseId, $userId)
     */
    public function calcLearnProcessByCourseIdAndUserId($courseId, $userId)
    {
        $activities = $this->getActivityDao()->findByCourseId($courseId);
        $activityIds = ArrayToolkit::column($activities, 'id');
        $daysCount = $this->getActivityLearnLogDao()->countLearnedDaysByActivityIdsAndUserId($activityIds, $userId);
        $learnedTime = 0;
        $learnedTimePerDay = $daysCount > 0 ? $learnedTime / $daysCount : 0;

        return [$daysCount, $learnedTime, $learnedTimePerDay];
    }

    public function deleteLearnLogsByActivityId($activityId)
    {
        $count = $this->getActivityLearnLogDao()->count(['activityId' => $activityId]);
        if ($count <= 1000) {
            return $this->getActivityLearnLogDao()->deleteByActivityId($activityId);
        }
        $this->getSchedulerService()->register([
            'name' => "activity_learn_log_delete_job_{$activityId}",
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => time(),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Activity\Job\DeleteActivityLearnLogJob',
            'args' => ['activityId' => $activityId],
        ]);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return ActivityLearnLogDao
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
