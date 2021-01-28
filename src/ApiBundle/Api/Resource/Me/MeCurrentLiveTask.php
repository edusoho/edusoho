<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;

class MeCurrentLiveTask extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $liveNotifySetting = $this->getSettingService()->get('homepage_live_notify', array());
        if (empty($liveNotifySetting['enabled'])) {
            return (object) array();
        }
        $startTime = time() + $liveNotifySetting['preTime'] * 60;
        $endTimeRange = 15 * 60; //结束前固定15分钟
        $task = $this->getTaskService()->getUserCurrentPublishedLiveTask($user['id'], $startTime, $endTimeRange);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $task['activity'] = $this->getActivityService()->getActivity($task['activityId'], true);

        return $task;
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Course:TaskService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
