<?php

namespace ApiBundle\Api\Resource\LiveStatistic;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\LiveActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\LiveStatistics\Service\Impl\LiveCloudStatisticsServiceImpl;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\User\Service\UserService;

class LiveStatisticMember extends AbstractResource
{
    public function search(ApiRequest $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($task)) {
            TaskException::NOTFOUND_TASK();
        }
        $this->getCourseService()->tryManageCourse($task['courseId']);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if (empty($activity['ext']['liveId'])) {
            LiveActivityException::NOTFOUND_LIVE();
        }
        $this->getLiveStatisticsService()->getLiveMemberData($task);

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getLiveStatisticsService()->searchCourseMemberLiveData(['courseId' => $task['courseId'], 'liveId' => $activity['ext']['liveId']], $offset, $limit);

        return $this->makePagingObject($this->processMemberData($activity, $members), $this->getCourseMemberService()->countMembers(['courseId' => $task['courseId']]), $offset, $limit);
    }

    public function processMemberData($activity, $members)
    {
        $cloudStatisticData = $activity['ext']['cloudStatisticData'];
        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        foreach ($members as &$member) {
            $member['truename'] = empty($userProfiles[$member['userId']]) ? '--' : $userProfiles[$member['userId']]['truename'];
            $member['nickname'] = empty($users[$member['userId']]) ? '--' : $users[$member['userId']]['nickname'];
            $member['email'] = empty($users[$member['userId']]) || empty($users[$member['userId']]['emailVerified']) ? '--' : $users[$member['userId']]['email'];
            $member['checkinNum'] = empty($cloudStatisticData['checkinNum']) ? '--' : $member['checkinNum'].'/'.$cloudStatisticData['checkinNum'];
        }

        return $members;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return LiveCloudStatisticsServiceImpl
     */
    protected function getLiveStatisticsService()
    {
        return $this->service('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
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
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
