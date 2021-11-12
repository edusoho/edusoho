<?php

namespace Biz\LiveStatistics\Job;

use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\LiveStatistics\Dao\LiveMemberStatisticsDao;
use Biz\LiveStatistics\Service\LiveCloudStatisticsService;
use Biz\Task\Service\TaskService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Topxia\Service\Common\ServiceKernel;

class SyncLiveMemberDataJob extends AbstractJob
{
    const LIMIT = 500;

    const FINISH = 99999999999999;

    const ES_CLOUD_LIVE_PROVIDER = 13;

    protected $EdusohoLiveClient = null;

    protected $requestTime = 0;

    protected $start = 0;

    protected $activityId = 0;

    protected $syncLive = 0;

    public function execute()
    {
        $this->requestTime = time();
        $this->activityId = $this->args['activityId'];
        $this->start = empty($this->args['start']) ? 0 : $this->args['start'];
        $this->syncLive = empty($this->args['syncLiveDetail']) ? 0 : $this->args['syncLiveDetail'];
        $activity = $this->getActivityService()->getActivity($this->activityId, true);
        $client = new EdusohoLiveClient();
        $this->EdusohoLiveClient = $client;
        while (time() - $this->requestTime < 90) {
            $this->getLiveStatistic($activity);
            $this->start += self::LIMIT;
        }
        $this->createdSyncJob();
    }

    protected function createdSyncJob()
    {
        if (self::FINISH != $this->requestTime) {
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
        try {
            $this->getGeneralLiveMemberStatistics($activity);
            $this->getESLiveMemberStatistics($activity);
        } catch (\RuntimeException $e) {
        }
    }

    /**
     * @param $activity //其他直播数据 ，隔天
     */
    protected function getGeneralLiveMemberStatistics($activity)
    {
        if (self::ES_CLOUD_LIVE_PROVIDER == $activity['ext']['liveProvider'] || ($activity['endTime'] > time() && date('Y-m-d', time()) == date('Y-m-d', $activity['endTime']))) {
            return;
        }
        try {
            $memberData = $this->EdusohoLiveClient->getLiveStudentStatistics($activity['ext']['liveId'], ['start' => 0, 'limit' => self::LIMIT]);
        } catch (CloudAPIIOException $cloudAPIIOException) {
        }

        $this->processGeneralLiveMemberData($activity, $memberData);
        if (count($memberData['list']) < self::LIMIT) {
            $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($activity['ext']['cloudStatisticData'], ['memberFinished' => 1])]);
            $this->requestTime = self::FINISH;
            if ($this->syncLive) {
                $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
                $this->getLiveCloudStatisticsService()->getLiveData($task);
            }
        }
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

        $this->getLiveCloudStatisticsService()->processEsLiveMemberData($activity, $memberData);
        if (count($memberData['data']) < self::LIMIT) {
            $this->requestTime = self::FINISH;
        }
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return ServiceKernel::instance()->createService('Task:TaskService');
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
