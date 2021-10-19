<?php

namespace Biz\LiveStatistics\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\LiveActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Course\Service\CourseService;
use Biz\LiveStatistics\Dao\LiveMemberStatisticsDao;
use Biz\LiveStatistics\Service\LiveCloudStatisticsService;
use Biz\System\Service\CacheService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class LiveCloudStatisticsServiceImpl extends BaseService implements LiveCloudStatisticsService
{
    const ES_CLOUD_LIVE_PROVIDER = 13;

    const LIMIT = 150;

    protected $EdusohoLiveClient = null;

    public function searchCourseMemberLiveData($conditions, $start, $limit)
    {
        if (!ArrayToolkit::requireds($conditions, ['courseId', 'liveId'])) {
            return ['message' => '缺少必要字段'];
        }

        return $this->getLiveMemberStatisticsDao()->searchLiveMembersJoinCourseMember($conditions, $start, $limit);
    }

    public function getLiveData($task)
    {
        $course = $this->getCourseService()->tryManageCourse($task['courseId']);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $cloudStatisticData = $activity['ext']['cloudStatisticData'];
        if (empty($activity['ext']['liveId'])) {
            LiveActivityException::NOTFOUND_LIVE();
        }
        //频次控制， 直播未结束 允许最多3分钟请求云平台
        if (!empty($cloudStatisticData['requestTime']) && time() - $cloudStatisticData['requestTime'] < 180) {
            return  $cloudStatisticData;
        }
        //频次控制， 直播已结束且未超过24小时 允许最多15分钟请求云平台
        if (time() > $activity['endTime'] && time() - $activity['endTime'] < 24 * 3600 && !empty($cloudStatisticData['requestTime']) && time() - $cloudStatisticData['requestTime'] < 900) {
            return  $cloudStatisticData;
        }
        //频次控制， 直播已结束且数据以获取结束(获取时间超过结束时间且数据收集结束) 直接返回数据
        if (!empty($cloudStatisticData['detailFinished'])) {
            return  $cloudStatisticData;
        }

        $user = $this->getUserService()->getUser($course['teacherIds'][0]);
        $profile = $this->getUserService()->getUserProfile($course['teacherIds'][0]);
        $client = new EdusohoLiveClient();
        $this->EdusohoLiveClient = $client;
        $data = [
            'teacher' => empty($profile['truename']) ? $user['nickname'] : $profile['truename'],
            'startTime' => $activity['startTime'],
            'endTime' => $activity['endTime'],
            'length' => $activity['length'],
            'requestTime' => time(),
        ];
        $this->getGeneralLiveStatistics($activity, $task, $data);
        $this->getESLiveStatistics($activity, $task, $data);
        $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($cloudStatisticData, $data)]);

        return $data;
    }

    public function getLiveMemberData($task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $startJob = [
            'name' => 'SyncLiveMemberDataJob'.$activity['id'].'_'.time(),
            'expression' => time() - 100,
            'class' => 'Biz\LiveStatistics\Job\SyncLiveMemberDataJob',
            'misfire_threshold' => 10 * 60,
            'args' => [
                'activityId' => $activity['id'],
                'start' => self::LIMIT,
            ],
        ];
        $this->getSchedulerService()->register($startJob);

        return;

        $cloudStatisticData = $activity['ext']['cloudStatisticData'];
        //频次控制， 直播未结束 允许最多3分钟请求云平台
        if (!empty($cloudStatisticData['memberRequestTime']) && time() - $cloudStatisticData['memberRequestTime'] < 180) {
            return  $cloudStatisticData;
        }
        //频次控制， 直播已结束且未超过24小时 允许最多15分钟请求云平台
        if (time() > $activity['endTime'] && time() - $activity['endTime'] < 24 * 3600 && !empty($cloudStatisticData['memberRequestTime']) && time() - $cloudStatisticData['memberRequestTime'] < 900) {
            return  $cloudStatisticData;
        }
        //频次控制， 直播已结束且数据已获取结束(获取时间超过结束时间且数据收集结束) 直接返回数据
        if (!empty($cloudStatisticData['memberFinished'])) {
            return  $cloudStatisticData;
        }
        $client = new EdusohoLiveClient();
        $this->EdusohoLiveClient = $client;
        $this->getGeneralLiveMemberStatistics($activity);
        $this->getESLiveMemberStatistics($activity);
        $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($activity['ext']['cloudStatisticData'], ['memberRequestTime' => time()])]);
    }

    public function batchCreateLiveMemberData($data)
    {
        if (empty($data)) {
            return;
        }
        try {
            $this->getLiveMemberStatisticsDao()->batchCreate($data);
        } catch (\RuntimeException $e) {
        }
    }

    public function batchUpdateLiveMemberData($data)
    {
        if (empty($data)) {
            return;
        }
        try {
            $this->getLiveMemberStatisticsDao()->batchUpdate(array_keys($data), $data, 'id');
        } catch (\RuntimeException $e) {
        }
    }

    /**
     * @param $activity //其他直播数据 ，隔天
     */
    protected function getGeneralLiveMemberStatistics($activity)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER == $activity['ext']['liveProvider'] || ($activity['endTime'] > time() || date('Y-m-d', time()) == date('Y-m-d', $activity['endTime']))) {
            return;
        }
        try {
            $memberData = $this->EdusohoLiveClient->getLiveStudentStatistics($activity['ext']['liveId'], ['start' => 0, 'limit' => self::LIMIT]);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }
        if (empty($memberData['list'])) {
            return;
        }
        $createData = [];
        $updateData = [];
        $userIds = ArrayToolkit::column($memberData['list'], 'userId');
        $members = $this->getLiveMemberStatisticsDao()->search(['userIds' => empty($userIds) ? [-1] : $userIds, 'liveId' => $activity['ext']['liveId'], 'courseId' => $activity['fromCourseId']], [], 0, count($userIds), ['id', 'userId']);
        $members = ArrayToolkit::index($members, 'userId');
        foreach ($memberData['list'] as $member) {
            $data = [
                'courseId' => $activity['fromCourseId'],
                'userId' => $member['userId'],
                'liveId' => $activity['ext']['liveId'],
                'firstEnterTime' => $member['joinTime'],
                'watchDuration' => $member['onlineDuration'],
                'checkinNum' => $member['checkinNumber'],
                'chatNum' => $member['chatNumber'],
                'answerNum' => '--',
            ];
            if (!empty($members[$member['userId']])) {
                $updateData[$members[$member['userId']]['id']] = $data;
                continue;
            }
            $createData[] = $data;
        }
        if (!empty($memberData['list']) && time() - $activity['endTime'] > 2 * 3600) {
            $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($activity['ext']['cloudStatisticData'], ['memberFinished' => 1])]);
        }
        $this->batchCreateLiveMemberData($createData);
        $this->batchUpdateLiveMemberData($updateData);
        $this->createdSyncMemberDataJob($activity, $memberData);
    }

    /**
     * @param $activity //自研直播数据单独处理，实时
     */
    protected function getESLiveMemberStatistics($activity)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER != $activity['ext']['liveProvider']) {
            return;
        }
        try {
            $memberData = $this->EdusohoLiveClient->getEsLiveMembers($activity['ext']['liveId'], ['start' => 0, 'limit' => self::LIMIT]);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }

        if (empty($memberData['data'])) {
            return;
        }
        $createData = [];
        $updateData = [];
        $userIds = ArrayToolkit::column($memberData['data'], 'userId');
        $members = $this->getLiveMemberStatisticsDao()->search(['userIds' => empty($userIds) ? [-1] : $userIds, 'liveId' => $activity['ext']['liveId'], 'courseId' => $activity['fromCourseId']], [], 0, count($userIds), ['id', 'userId']);
        $members = ArrayToolkit::index($members, 'userId');
        foreach ($memberData['data'] as $member) {
            $data = [
                'courseId' => $activity['fromCourseId'],
                'userId' => $member['userId'],
                'liveId' => $activity['ext']['liveId'],
                'firstEnterTime' => $member['firstEnterTime'],
                'watchDuration' => $member['watchDuration'],
                'checkinNum' => $member['checkinNum'],
                'chatNum' => empty($member['chatNum']) ? 0 : $member['chatNum'],
                'answerNum' => empty($member['answerNum']) ? 0 : $member['answerNum'],
                'requestTime' => time(),
            ];
            if (!empty($members[$member['userId']])) {
                $updateData[$members[$member['userId']]['id']] = $data;
                continue;
            }
            $createData[] = $data;
        }
        if (time() - $activity['endTime'] > 2 * 3600) {
            $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($activity['ext']['cloudStatisticData'], ['memberFinished' => 1])]);
        }
        $this->batchCreateLiveMemberData($createData);
        $this->batchUpdateLiveMemberData($updateData);
        $this->createdSyncMemberDataJob($activity, $memberData);
    }

    protected function createdSyncMemberDataJob($activity, $memberData)
    {
        if ($memberData['total'] <= self::LIMIT) {
            return [];
        }
        $startJob = [
            'name' => 'SyncLiveMemberDataJob'.$activity['id'].'_'.time(),
            'expression' => time() - 100,
            'class' => 'Biz\LiveStatistics\Job\SyncLiveMemberDataJob',
            'misfire_threshold' => 10 * 60,
            'args' => [
                'activityId' => $activity['id'],
                'start' => self::LIMIT,
            ],
        ];
        $this->getSchedulerService()->register($startJob);
    }

    /**
     * @param $activity
     * @param $task
     * @param $data
     * //其他数据 隔天
     */
    protected function getGeneralLiveStatistics($activity, $task, &$data)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER == $activity['ext']['liveProvider']) {
            return;
        }
        try {
            $cloudData = $this->EdusohoLiveClient->getLiveStatistics($activity['ext']['liveId']);
            $onlineData = $this->EdusohoLiveClient->getMaxOnline($activity['ext']['liveId']);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }
        $data['memberNumber'] = empty($cloudData['onlineNumber']) ? 0 : $cloudData['onlineNumber'];
        $data['chatNumber'] = empty($cloudData['chatNumber']) ? 0 : $cloudData['chatNumber'];
        $data['checkinNum'] = empty($cloudData['checkinBatchNumber']) ? 0 : $cloudData['checkinBatchNumber'];
        $data['maxOnlineNumber'] = empty($onlineData['onLineNum']) ? 0 : $onlineData['onLineNum'];

        if ((!empty($cloudData['onlineNumber']) && time() - $activity['endTime'] > 2 * 3600) || time() - $activity['endTime'] > 24 * 3600) {
            $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($activity['ext']['cloudStatisticData'], ['detailFinished' => 1])]);
        }
    }

    /**
     * @param $activity
     * @param $task
     * @param $data
     * //自研直播数据 ，实时
     */
    protected function getESLiveStatistics($activity, $task, &$data)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER != $activity['ext']['liveProvider']) {
            return;
        }
        try {
            $cloudData = $this->EdusohoLiveClient->getEsLiveInfo($activity['ext']['liveId']);
            $memberData = $this->EdusohoLiveClient->getEsLiveMembers($activity['ext']['liveId'], ['start' => 0, 'limit' => 1]);
            if ($task['endTime'] > time() && date('Y-m-d', time()) != date('Y-m-d', $task['endTime'])) {
                $chatData = $this->EdusohoLiveClient->getLiveStatistics($activity['ext']['liveId']);
            }
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }
        $data['startTime'] = empty($cloudData['actualStartTime']) ? $data['startTime'] : $cloudData['actualStartTime'];
        $data['endTime'] = empty($cloudData['actualEndTime']) ? $data['endTime'] : $cloudData['actualEndTime'];
        $data['maxOnlineNumber'] = empty($cloudData['maxOnlineNum']) ? 0 : $cloudData['maxOnlineNum'];
        $data['checkinNum'] = empty($cloudData['checkinNum']) ? 0 : $cloudData['checkinNum'];
        $data['chatNumber'] = empty($chatData['chatNumber']) ? 0 : $chatData['chatNumber'];
        $data['memberNumber'] = empty($memberData['total']) ? 0 : $memberData['total'];

        if (time() - $activity['endTime'] > 2 * 3600) {
            $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($activity['ext']['cloudStatisticData'], ['detailFinished' => 1])]);
        }
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return LiveMemberStatisticsDao
     */
    protected function getLiveMemberStatisticsDao()
    {
        return $this->createDao('LiveStatistics:LiveMemberStatisticsDao');
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return $this->createDao('Activity:LiveActivityDao');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }
}
