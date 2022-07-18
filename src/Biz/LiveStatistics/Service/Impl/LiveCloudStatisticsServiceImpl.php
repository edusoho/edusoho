<?php

namespace Biz\LiveStatistics\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\LiveActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\BaseService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
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

    const LIMIT = 300;

    /**
     * @var EdusohoLiveClient
     */
    protected $EdusohoLiveClient = null;

    /**
     * @param $conditions
     * @param $start
     * @param $limit
     *
     * @return string[]
     */
    public function searchCourseMemberLiveData($conditions, $start, $limit)
    {
        if (!ArrayToolkit::requireds($conditions, ['courseId', 'liveId'])) {
            return ['message' => '缺少必要字段'];
        }

        return $this->getLiveMemberStatisticsDao()->searchLiveMembersJoinCourseMember($conditions, $start, $limit);
    }

    public function sumWatchDurationByCourseIdGroupByUserId($courseId)
    {
        $liveIds = array_column($this->getLiveActivityDao()->findByIds(array_column($this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'live'), 'mediaId')), 'liveId');
        $members = ArrayToolkit::group($this->getLiveMemberStatisticsDao()->findMembersByLiveIds($liveIds), 'userId');
        if (empty($members)) {
            return [];
        }
        $sumWatchDurations = [];
        foreach ($members as $userId => $member) {
            $sumWatchDurations[$userId] = array_sum(array_column($member, 'watchDuration'));
        }

        return $sumWatchDurations;
    }

    public function countLiveMembersByLiveId($liveId)
    {
        return $this->getLiveMemberStatisticsDao()->count(['liveId' => $liveId]);
    }

    public function countLiveMembers($conditions)
    {
        return $this->getLiveMemberStatisticsDao()->count($conditions);
    }

    public function getAvgWatchDurationByLiveId($liveId, $userIds)
    {
        $sum = $this->getLiveMemberStatisticsDao()->sumWatchDurationByLiveId($liveId, $userIds);
        $count = $this->getLiveMemberStatisticsDao()->count(['liveId' => $liveId, 'userIds' => $userIds]);

        return empty($count) ? 0 : round($sum / ($count * 60), 1);
    }

    public function sumChatNumByLiveId($liveId, $userIds)
    {
        $result = $this->getLiveMemberStatisticsDao()->sumChatNumByLiveId($liveId, $userIds);

        return empty($result) ? 0 : $result;
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
            return $cloudStatisticData;
        }
        //频次控制， 直播已结束允许最多30分钟请求云平台
        if (($activity['ext']['liveEndTime'] < time() || 'closed' == $activity['ext']['progressStatus']) && !empty($cloudStatisticData['requestTime']) && time() - $cloudStatisticData['requestTime'] < 1800) {
            return $cloudStatisticData;
        }

        $client = new EdusohoLiveClient();
        $this->EdusohoLiveClient = $client;
        $data = [
            'teacherId' => empty($activity['ext']['anchorId']) ? (empty($course['teacherIds']) ? 0 : $course['teacherIds'][0]) : $activity['ext']['anchorId'],
            'startTime' => empty($activity['ext']['liveStartTime']) ? $activity['startTime'] : $activity['ext']['liveStartTime'],
            'endTime' => empty($activity['ext']['liveEndTime']) ? $activity['endTime'] : $activity['ext']['liveEndTime'],
            'length' => empty($activity['ext']['liveEndTime']) ? $activity['length'] : round(($activity['ext']['liveEndTime'] - $activity['ext']['liveStartTime']) / 60, 1),
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

        $cloudStatisticData = $activity['ext']['cloudStatisticData'];
        //频次控制， 直播未结束 允许最多3分钟请求云平台
        if (!empty($cloudStatisticData['memberRequestTime']) && time() - $cloudStatisticData['memberRequestTime'] < 180) {
            return;
        }
        //频次控制， 直播已结束且未超过24小时 允许最多30分钟请求云平台
        if (($activity['ext']['liveEndTime'] < time() || 'closed' == $activity['ext']['progressStatus']) && !empty($cloudStatisticData['memberRequestTime']) && time() - $cloudStatisticData['memberRequestTime'] < 1800) {
            return;
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
            return;
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
            return;
        }
    }

    /**
     * @param $activity
     * @param $memberData //云接口数据
     * //处理自研直播
     */
    public function processEsLiveMemberData($activity, $memberData)
    {
        if (empty($memberData['data'])) {
            return;
        }
        $createData = [];
        $updateData = [];
        $userIds = ArrayToolkit::column($memberData['data'], 'userId');
        $members = $this->getLiveMemberStatisticsDao()->search(['userIds' => empty($userIds) ? [-1] : $userIds, 'liveId' => $activity['ext']['liveId']], [], 0, count($userIds), ['id', 'userId']);
        $members = ArrayToolkit::index($members, 'userId');
        $count = $this->getUserDao()->count([]);
        foreach ($memberData['data'] as $member) {
            $userId = $member['userId'];
            if ($userId == $activity['ext']['anchorId']) {
                continue;
            }
            if ($userId > $count && !empty($member['userName'])) {
                $baseUser = $this->getUserDao()->getByNickname($member['userName']);
                if (empty($baseUser)) {
                    continue;
                }
                $userId = $baseUser['id'];
            }

            $data = [
                'userId' => $userId,
                'liveId' => $activity['ext']['liveId'],
                'firstEnterTime' => $member['firstEnterTime'],
                'watchDuration' => $member['watchDuration'],
                'checkinNum' => $member['checkinNum'],
                'chatNum' => empty($member['chatNum']) ? 0 : $member['chatNum'],
                'answerNum' => empty($member['answerNum']) ? 0 : $member['answerNum'],
                'requestTime' => time(),
            ];
            if (!empty($members[$userId])) {
                $updateData[$members[$userId]['id']] = $data;
                continue;
            }
            $createData[] = $data;
        }
        $this->batchCreateLiveMemberData($createData);
        $this->batchUpdateLiveMemberData($updateData);
    }

    /**
     * @param $activity
     * @param $memberData //云接口数据
     * //处理非自研直播
     */
    public function processGeneralLiveMemberData($activity, $memberData)
    {
        if (empty($memberData['list'])) {
            return;
        }
        $createData = [];
        $updateData = [];
        $userIds = ArrayToolkit::column($memberData['list'], 'studentId');
        $members = $this->getLiveMemberStatisticsDao()->search(['userIds' => empty($userIds) ? [-1] : $userIds, 'liveId' => $activity['ext']['liveId']], [], 0, count($userIds), ['id', 'userId']);
        $members = ArrayToolkit::index($members, 'userId');
        $count = $this->getUserDao()->count([]);
        foreach ($memberData['list'] as $member) {
            if ($member['studentId'] == $activity['ext']['anchorId'] || $member['studentId'] > $count) {
                continue;
            }
            $data = [
                'userId' => $member['studentId'],
                'liveId' => $activity['ext']['liveId'],
                'firstEnterTime' => $member['joinTime'],
                'watchDuration' => $member['onlineDuration'],
                'checkinNum' => $member['checkinNumber'],
                'chatNum' => $member['chatNumber'],
                'answerNum' => empty($member['answerNum']) ? 0 : $member['answerNum'],
                'requestTime' => time(),
            ];
            if (!empty($members[$member['studentId']])) {
                $updateData[$members[$member['studentId']]['id']] = $data;
                continue;
            }
            $createData[] = $data;
        }
        $this->batchCreateLiveMemberData($createData);
        $this->batchUpdateLiveMemberData($updateData);
    }

    /**
     * @param $activity //其他直播数据 ，隔天；用于定时任务
     */
    protected function getGeneralLiveMemberStatistics($activity)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER == $activity['ext']['liveProvider'] || $activity['endTime'] > time() || date('Y-m-d', time()) == date('Y-m-d', $activity['endTime'])) {
            return;
        }
        try {
            $memberData = $this->EdusohoLiveClient->getLiveStudentStatistics($activity['ext']['liveId'], ['start' => 0, 'limit' => self::LIMIT]);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }
        $this->processGeneralLiveMemberData($activity, $memberData);
        $this->createdSyncMemberDataJob($activity, $memberData);
    }

    /**
     * @param $activity //自研直播数据单独处理，实时
     */
    protected function getESLiveMemberStatistics($activity)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER != $activity['ext']['liveProvider'] || $activity['startTime'] > time()) {
            return;
        }
        try {
            $memberData = $this->EdusohoLiveClient->getEsLiveMembers($activity['ext']['liveId'], ['start' => 0, 'limit' => self::LIMIT]);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }
        $this->processEsLiveMemberData($activity, $memberData);
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
        if (self::ES_CLOUD_LIVE_PROVIDER == $activity['ext']['liveProvider'] || $activity['startTime'] > time() || date('Y-m-d', time()) == date('Y-m-d', $activity['endTime'])) {
            return;
        }
        try {
            $cloudData = $this->EdusohoLiveClient->getLiveStatistics($activity['ext']['liveId']);
            $onlineData = $this->EdusohoLiveClient->getMaxOnline($activity['ext']['liveId']);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }
        $data['chatNumber'] = empty($cloudData['chatNumber']) ? 0 : $cloudData['chatNumber'];
        $data['checkinNum'] = empty($cloudData['checkinBatchNumber']) ? 0 : $cloudData['checkinBatchNumber'];
        $data['maxOnlineNumber'] = empty($onlineData['onLineNum']) ? 0 : $onlineData['onLineNum'];
    }

    /**
     * @param $activity
     * @param $task
     * @param $data
     * //自研直播数据 ，实时
     */
    protected function getESLiveStatistics($activity, $task, &$data)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER != $activity['ext']['liveProvider'] || $activity['startTime'] > time()) {
            return;
        }
        try {
            $cloudData = $this->EdusohoLiveClient->getEsLiveInfo($activity['ext']['liveId']);

            $memberData = $this->EdusohoLiveClient->getEsLiveMembers($activity['ext']['liveId'], ['start' => 0, 'limit' => 1]);
            $liveBatch = $this->EdusohoLiveClient->getLiveCheckBatchData($activity['ext']['liveId'], []);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }
        $data['startTime'] = empty($cloudData['actualStartTime']) ? $data['startTime'] : $cloudData['actualStartTime'];
        $data['endTime'] = empty($cloudData['actualEndTime']) ? $data['endTime'] : $cloudData['actualEndTime'];
        $data['maxOnlineNumber'] = empty($cloudData['maxOnlineNum']) ? 0 : $cloudData['maxOnlineNum'];
        $data['checkinNum'] = empty($liveBatch) ? 0 : count($liveBatch);
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

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }
}
