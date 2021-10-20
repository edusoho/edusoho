<?php

namespace Biz\LiveStatistics\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\LiveStatistics\Dao\LiveMemberStatisticsDao;
use Biz\LiveStatistics\Service\LiveCloudStatisticsService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Topxia\Service\Common\ServiceKernel;

class SyncLiveMemberDataJob extends AbstractJob
{
    const LIMIT = 500;

    const FINISH = 111;

    const ES_CLOUD_LIVE_PROVIDER = 13;

    protected $EdusohoLiveClient = null;

    protected $requestTimes = 0;

    protected $start = 0;

    protected $activityId = 0;

    public function execute()
    {
        $this->activityId = $this->args['activityId'];
        $this->start = empty($this->args['start']) ? 0 : $this->args['start'];
        $activity = $this->getActivityService()->getActivity($this->activityId, true);
        $client = new EdusohoLiveClient();
        $this->EdusohoLiveClient = $client;
        while ($this->requestTimes < 5) {
            $this->getLiveStatistic($activity);
            $this->start += self::LIMIT;
        }
    }

    protected function createdSyncJob()
    {
        if (self::FINISH != $this->requestTimes) {
            $startJob = [
                'name' => 'SyncLiveMemberDataJob'.$this->activityId.'_'.time(),
                'expression' => time() - 100,
                'class' => 'Biz\LiveStatistics\Job\SyncLiveMemberDataJob',
                'misfire_threshold' => 10 * 60,
                'args' => [
                    'activityId' => $this->activityId,
                    'start' => $this->start,
                ],
            ];
            $this->getSchedulerService()->register($startJob);
        }
    }

    protected function getLiveStatistic($activity)
    {
        $this->getGeneralLiveMemberStatistics($activity);
        $this->getESLiveMemberStatistics($activity);
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
                'chatNumber' => $member['chatNumber'],
                'answerNum' => empty($member['answerNum']) ? 0 : $member['answerNum'],
                'requestTime' => time(),
            ];
            if (!empty($members[$member['userId']])) {
                $updateData[$members[$member['userId']]['id']] = $data;
                continue;
            }
            $createData[] = $data;
        }
        if (count($memberData['list']) < self::LIMIT) {
            $this->requestTimes = self::FINISH;
        }
        $this->getLiveCloudStatisticsService()->batchCreateLiveMemberData($createData);
        $this->getLiveCloudStatisticsService()->batchUpdateLiveMemberData($updateData);
        ++$this->requestTimes;
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
                'chatNumber' => empty($member['chatNumber']) ? 0 : $member['chatNumber'],
                'answerNum' => empty($member['answerNum']) ? 0 : $member['answerNum'],
                'requestTime' => time(),
            ];
            if (!empty($members[$member['userId']])) {
                $updateData[$members[$member['userId']]['id']] = $data;
                continue;
            }
            $createData[] = $data;
        }
        if (count($memberData['data']) < self::LIMIT) {
            $this->requestTimes = self::FINISH;
        }
        $this->getLiveCloudStatisticsService()->batchCreateLiveMemberData($createData);
        $this->getLiveCloudStatisticsService()->batchUpdateLiveMemberData($updateData);
        ++$this->requestTimes;
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return ServiceKernel::instance()->createService('Scheduler:SchedulerService');
    }

    /**
     * @return LiveCloudStatisticsService
     */
    protected function getLiveCloudStatisticsService()
    {
        return ServiceKernel::instance()->createService('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return ServiceKernel::instance()->createDao('Activity:LiveActivityDao');
    }

    /**
     * @return LiveMemberStatisticsDao
     */
    protected function getLiveMemberStatisticsDao()
    {
        return ServiceKernel::instance()->createDao('LiveStatistics:LiveMemberStatisticsDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return ServiceKernel::instance()->createService('Activity:ActivityService');
    }
}
