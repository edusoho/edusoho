<?php

namespace ApiBundle\Api\Resource\Task;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\DeviceToolkit;
use Biz\Course\MemberException;
use Biz\Course\Service\MemberService;
use Biz\Live\Service\LiveService;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\Task\TaskException;

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
        $params = [];
        $params['id'] = $user['id'];
        $params['displayName'] = $user['nickname'];
        $params['nickname'] = $user['nickname'].'_'.$user['id'];
        $params['role'] = $this->getCourseMemberService()->getUserLiveroomRoleByCourseIdAndUserId($task['courseId'], $user['id']);
        // android, iphone, mobile
        $params['device'] = $request->request->get('device', DeviceToolkit::isMobileClient() ? 'mobile' : 'desktop');
        $liveGroup = $this->getMultiClassGroupService()->getLiveGroupByUserIdAndCourseId($user['id'], $task['courseId'], $activity['ext']['liveId']);
        if (!empty($liveGroup)) {
            $params['groupCode'] = $liveGroup['live_code'];
        }
        if (!empty($activity['syncId'])) {
            $liveTicket = $this->getS2B2CFacadeService()->getS2B2CService()->getLiveEntryTicket($activity['ext']['liveId'], $params);
        } else {
            $liveTicket = $this->getLiveService()->createLiveTicket($activity['ext']['liveId'], $params);
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
            $liveTicket = $this->getLiveService()->getLiveTicket($activity['ext']['liveId'], $liveTicket);
        }

        return $liveTicket;
    }

    /**
     * @return LiveService
     */
    protected function getLiveService()
    {
        return $this->service('Live:LiveService');
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

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->service('MultiClass:MultiClassGroupService');
    }
}
