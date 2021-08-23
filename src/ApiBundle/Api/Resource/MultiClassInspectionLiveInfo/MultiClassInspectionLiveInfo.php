<?php

namespace ApiBundle\Api\Resource\MultiClassInspectionLiveInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\ActivityException;
use Biz\Activity\LiveActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Biz\Util\EdusohoLiveClient;

class MultiClassInspectionLiveInfo extends AbstractResource
{
    public function get(ApiRequest $request, $activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);
        if (!$activity) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }

        if ('live' != $activity['mediaType']) {
            throw LiveActivityException::NOTFOUND_LIVE();
        }

        if (EdusohoLiveClient::SELF_ES_LIVE_PROVIDER != $activity['ext']['liveProvider']) {
            throw LiveActivityException::LIVE_PROVIDER_NOT_SUPPORT();
        }

        try {
            $info = $this->getLiveClient()->getLiveRoomRealTimeInfo($activity['ext']['liveId']);
            if ($info['base']['startTime'] <= time() && 'unstart' == $info['info']['status']) {
                $info['info']['status'] = 'notOnTime';
            }
            $info = $this->appendLiveUrl($info, $activity);
        } catch (\Exception $e) {
            throw $e;
        }

        return $info;
    }

    protected function appendLiveUrl($info, $activity)
    {
        switch ($info['info']['status']) {
            case 'living':
                $info['info']['viewUrl'] = $this->generateUrl('task_live_entry', ['courseId' => $activity['fromCourseId'], 'activityId' => $activity['id']]);
                break;
            case 'finished':
                if (in_array($activity['ext']['replayStatus'], ['generated', 'videoGenerated'])) {
                    $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
                    $info['info']['viewUrl'] = $this->generateUrl('course_task_show', ['courseId' => $activity['fromCourseId'], 'id' => $task['id']]);
                }
                break;
            default:
                $info['info']['viewUrl'] = '';
        }

        return $info;
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return EdusohoLiveClient
     */
    protected function getLiveClient()
    {
        return $this->biz['educloud.live_client'];
    }
}
