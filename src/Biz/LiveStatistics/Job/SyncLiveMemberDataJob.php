<?php

namespace Biz\LiveStatistics\Job;

use AppBundle\Common\DateToolkit;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Live\Service\LiveService;
use Biz\LiveStatistics\Service\LiveCloudStatisticsService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class SyncLiveMemberDataJob extends AbstractJob
{
    const LIMIT = 500;

    protected $finish = 0;

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
        //单job执行时间限制90秒
        while (time() - $this->requestTime < 90 && 0 == $this->finish) {
            $this->syncLiveStatistic($activity);
            $this->start += self::LIMIT;
        }
        $this->createdSyncJob();
    }

    protected function createdSyncJob()
    {
        if (0 == $this->finish) {
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

    private function syncLiveStatistic($activity)
    {
        try {
            if ($this->getLiveService()->isESLive($activity['ext']['liveProvider'])) {
                $this->syncESLiveMemberStatistics($activity);
            } else {
                $this->syncThirdPartyLiveMemberStatistics($activity);
            }
        } catch (\RuntimeException $e) {
        }
    }

    /**
     * @param $activity //其他直播数据 ，隔天
     */
    private function syncThirdPartyLiveMemberStatistics($activity)
    {
        if (($activity['endTime'] > time() || DateToolkit::isToday($activity['endTime'])) && !$this->getLiveService()->isProviderStatisticInRealTime($activity['ext']['liveProvider'])) {
            $this->finish = 1;

            return;
        }
        try {
            $memberData = $this->getCloudLiveClient()->getLiveStudentStatistics($activity['ext']['liveId'], ['start' => $this->start, 'limit' => self::LIMIT]);
        } catch (CloudAPIIOException $cloudAPIIOException) {
            $this->finish = 1;

            return;
        }

        $this->getLiveCloudStatisticsService()->processThirdPartyLiveMemberData($activity['ext'], $memberData);

        if (!isset($memberData['list'])) {
            $this->finish = 1;

            return;
        }

        if (count($memberData['list']) < self::LIMIT) {
            $this->getLiveActivityDao()->update($activity['ext']['id'], ['cloudStatisticData' => array_merge($activity['ext']['cloudStatisticData'], ['memberFinished' => 1])]);
            $this->finish = 1;
        }
    }

    /**
     * @param $activity //自研直播数据单独处理，实时
     */
    private function syncESLiveMemberStatistics($activity)
    {
        try {
            $memberData = $this->getCloudLiveClient()->getEsLiveMembers($activity['ext']['liveId'], ['start' => $this->start, 'limit' => self::LIMIT]);
        } catch (CloudAPIIOException $cloudAPIIOException) {
            $this->finish = 1;

            return;
        }
        if (!isset($memberData['data'])) {
            $this->finish = 1;

            return;
        }
        $this->getLiveCloudStatisticsService()->processEsLiveMemberData($activity['ext'], $memberData);
        if (count($memberData['data']) < self::LIMIT) {
            $this->finish = 1;
        }
    }

    /**
     * @return EdusohoLiveClient
     */
    protected function getCloudLiveClient()
    {
        return $this->biz['educloud.live_client'];
    }

    /**
     * @return LiveService
     */
    private function getLiveService()
    {
        return $this->biz->service('Live:LiveService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return LiveCloudStatisticsService
     */
    protected function getLiveCloudStatisticsService()
    {
        return $this->biz->service('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return $this->biz->dao('Activity:LiveActivityDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
