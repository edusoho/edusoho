<?php

namespace ApiBundle\Api\Resource\LiveStatistic;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Live\LiveStatisticsException;
use Biz\Live\Service\LiveStatisticsService;
use Biz\LiveStatistics\Service\Impl\LiveCloudStatisticsServiceImpl;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\User\Service\UserService;

class LiveStatisticDetail extends AbstractResource
{
    public function search(ApiRequest $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($task)) {
            TaskException::NOTFOUND_TASK();
        }

        $result = $this->getLiveStatisticsService()->getLiveData($task);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $this->processJsonData($activity);

        return $this->buildResult($taskId, $result, $activity);
    }

    public function buildResult($taskId, $result, $activity)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $result['task'] = ArrayToolkit::parts($task, ['id', 'startTime', 'endTime', 'title', 'length']);
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $course['title'] = empty($course['title']) ? $course['courseSetTitle'] : $course['title'];
        $result['course'] = ArrayToolkit::parts($course, ['id', 'title', 'price', 'studentNum']);
        $result['teacherId'] = empty($result['teacherId']) ? 0 : $result['teacherId'];
        $user = empty($result['teacherId']) ? ['nickname' => '--'] : $this->getUserService()->getUser($result['teacherId']);
        $result['teacher'] = $user['nickname'];

        $members = $this->getMemberService()->searchMembers(['courseId' => $activity['fromCourseId']], [], 0, PHP_INT_MAX, ['userId']);
        $userIds = ArrayToolkit::column($members, 'userId');
        $userIds = array_diff($userIds, [$result['teacherId']]);
        $data = [
              'startTime' => $activity['startTime'],
              'endTime' => $activity['endTime'],
              'length' => round(($activity['ext']['liveEndTime'] - $activity['ext']['liveStartTime']) / 60, 1),
              'chatNumber' => $this->getLiveStatisticsService()->sumChatNumByLiveId($activity['ext']['liveId'], $userIds),
              'memberNumber' => empty($userIds) ? 0 : $this->getLiveStatisticsService()->countLiveMembers(['liveId' => $activity['ext']['liveId'], 'userIds' => $userIds]),
              'avgWatchTime' => empty($userIds) ? 0 : $this->getLiveStatisticsService()->getAvgWatchDurationByLiveId($activity['ext']['liveId'], $userIds),
       ];

        return array_merge($result, $data);
    }

    public function processJsonData($activity)
    {
        if ($activity['endTime'] > time() || date('Y-m-d', time()) == date('Y-m-d', $activity['endTime'])) {
            return;
        }
        try {
            $checkin = $this->getLiveStatisticsRollService()->updateCheckinStatistics($activity['ext']['liveId']);
            $visitor = $this->getLiveStatisticsRollService()->updateVisitorStatistics($activity['ext']['liveId']);
        } catch (LiveStatisticsException $e) {
        }
    }

    /**
     * @return LiveStatisticsService
     */
    protected function getLiveStatisticsRollService()
    {
        return $this->service('Live:LiveStatisticsService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return LiveCloudStatisticsServiceImpl
     */
    protected function getLiveStatisticsService()
    {
        return $this->service('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
