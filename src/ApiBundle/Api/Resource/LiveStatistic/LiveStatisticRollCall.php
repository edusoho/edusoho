<?php

namespace ApiBundle\Api\Resource\LiveStatistic;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\LiveActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\User\Service\UserService;

class LiveStatisticRollCall extends AbstractResource
{
    public function search(ApiRequest $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($task)) {
            TaskException::NOTFOUND_TASK();
        }
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if (empty($activity['ext']['liveId'])) {
            LiveActivityException::NOTFOUND_LIVE();
        }

        $status = $request->query->get('status');
        $statistics = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($activity['ext']['liveId']);
        if ($status && !empty($statistics['data']['detail'])) {
            $groupedStatistics = ArrayToolkit::group($statistics['data']['detail'], 'checkin');
            $groupedStatistics = [
                empty($groupedStatistics[0]) ? [] : $groupedStatistics[0],
                empty($groupedStatistics[1]) ? [] : $groupedStatistics[1],
            ];
            $statistics['data']['detail'] = 'checked' == $status ? $groupedStatistics[1] : $groupedStatistics[0];
        }

        $statistics = empty($statistics['data']['detail']) ? [] : $statistics['data']['detail'];
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $data = array_slice($statistics, $offset, $limit);

        return $this->makePagingObject($this->processStatisticData($data), count($statistics), $offset, $limit);
    }

    protected function processStatisticData($statistics)
    {
        $userIds = ArrayToolkit::column($statistics, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        foreach ($statistics as &$statistic) {
            $member['truename'] = empty($userProfiles[$statistic['userId']]) ? '--' : $userProfiles[$statistic['userId']]['truename'];
            $member['nickname'] = empty($users[$statistic['userId']]) ? '--' : $users[$statistic['userId']]['nickname'];
            $member['email'] = empty($users[$statistic['userId']]) || empty($users[$statistic['userId']]['emailVerified']) ? '--' : $users[$statistic['userId']]['email'];
            $member['checkin'] = empty($users[$statistic['userId']]) ? 0 : $users[$statistic['userId']]['checkin'];
            $member['mobile'] = empty($users[$member['userId']]) || empty($users[$member['userId']]['verifiedMobile']) ? '--' : $users[$statistic['userId']]['verifiedMobile'];
        }

        return $statistics;
    }

    /**
     * @return LiveStatisticsService
     */
    protected function getLiveStatisticsService()
    {
        return $this->service('Live:LiveStatisticsService');
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
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
