<?php

namespace ApiBundle\Api\Resource\Task;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\MemberException;
use Biz\Task\TaskException;
use AppBundle\Common\DeviceToolkit;

class TaskLiveTicket extends AbstractResource
{
    public function add(ApiRequest $request, $taskId)
    {
        $canLearn = $this->getCourseService()->canLearnTask($taskId);
        if ('success' != $canLearn['code']) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if ('live' != $task['type']) {
            throw TaskException::TYPE_INVALID();
        }

        $user = $this->getCurrentUser();
        $params = array();
        $params['id'] = $user['id'];
        $params['nickname'] = $user['nickname'].'_'.$user['id'];
        $params['role'] = 'student';
        // android, iphone, mobile
        $params['device'] = $request->request->get('device', DeviceToolkit::isMobileClient() ? 'mobile' : 'desktop');

        if (!empty($activity['syncId'])) {
            $liveTicket = $this->getS2B2CFacadeService()->getS2B2CService()->getLiveEntryTicket($activity['ext']['liveId'], $params);
        } else {
            $liveTicket = CloudAPIFactory::create('leaf')->post("/liverooms/{$activity['ext']['liveId']}/tickets", $params);
        }

        return $liveTicket;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $taskId, $liveTicket)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if (!empty($activity['syncId'])) {
            $liveTicket = $this->getS2B2CFacadeService()->getS2B2CService()->consumeLiveEntryTicket($activity['ext']['liveId'], $liveTicket);
        } else {
            $liveTicket = CloudAPIFactory::create('leaf')->get("/liverooms/{$activity['ext']['liveId']}/tickets/{$liveTicket}");
        }

        return $liveTicket;
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return \Biz\Task\Service\TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return \Biz\Activity\Service\ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
