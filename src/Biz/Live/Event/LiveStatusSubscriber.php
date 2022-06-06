<?php

namespace Biz\Live\Event;

use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\LiveStatistics\Service\Impl\LiveCloudStatisticsServiceImpl;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LiveStatusSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    const ES_LIVE_PROVIDER = 13;

    public static function getSubscribedEvents()
    {
        return [
            'live.status.start' => 'liveStatusStart',
            'live.status.close' => 'liveStatusClose',
            'live.activity.create' => 'liveActivityCreateStatus',
            'live.activity.update' => 'liveActivityUpdateStatus',
            'live.activity.delete' => 'liveActivityDeleteStatus',
        ];
    }

    public function liveStatusClose(Event $event)
    {
        $liveId = $event->getSubject();
        $liveActivity = $this->getLiveActivityDao()->getByLiveId($liveId);
        if (empty($liveActivity)) {
            return;
        }
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeJob'));
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeAgainJob'));
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeSecondJob'));
        $this->processLiveStatisticData($liveActivity);
    }

    protected function processLiveStatisticData($liveActivity)
    {
        $activities = $this->getActivityService()->findActivitiesByMediaIdsAndMediaType([$liveActivity['id']], 'live');
        $activities = array_values($activities);
        if (empty($activities)) {
            return;
        }
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activities[0]['fromCourseId'], $activities[0]['id']);
        if (empty($task)) {
            return;
        }
        $time = time();
        if (self::ES_LIVE_PROVIDER != $liveActivity['liveProvider']) {
            $time = strtotime(date('Y-m-d', strtotime('+1 day'))) + rand(18000, 21600);
        }
        $startJob = [
            'name' => 'SyncLiveMemberDataJob_'.$liveActivity['liveId'].time(),
            'expression' => $time,
            'class' => 'Biz\LiveStatistics\Job\SyncLiveMemberDataJob',
            'misfire_threshold' => 10 * 60,
            'args' => [
                'activityId' => $activities[0]['id'],
                'start' => 0,
            ],
        ];
        $this->getSchedulerService()->register($startJob);
    }

    public function liveStatusStart(Event $event)
    {
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($event->getSubject(), 'startJob'));
    }

    public function liveActivityCreateStatus(Event $event)
    {
        $this->createLiveStatusJobs($event->getSubject(), $event->getArgument('activity'));
    }

    public function liveActivityUpdateStatus(Event $event)
    {
        $this->createLiveStatusJobs($event->getArgument('liveId'), $event->getArgument('updateActivity'));
    }

    public function liveActivityDeleteStatus(Event $event)
    {
        $liveId = $event->getSubject();
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'startJob'));
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeJob'));
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeAgainJob'));
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeSecondJob'));
    }

    private function createLiveStatusJobs($liveId, $activity)
    {
        $startExecuteTime = (int)$activity['startTime'];
        $closeExecuteTime = (int)($activity['startTime'] + $activity['length'] * 60); //预定结束时间询问更新状态
        $closeExecuteAgainTime = (int)($activity['startTime'] + $activity['length'] * 60 + 3600); //预定结束时间一个小时后询问更新状态
        $closeExecuteSecondTime = (int)($activity['startTime'] + $activity['length'] * 60 + 7200); //预定结束时间两个小时 强制结束直播
        $this->registerLiveStatusJob($liveId, 'startJob', $startExecuteTime);
        $this->registerLiveStatusJob($liveId, 'closeJob', $closeExecuteTime);
        $this->registerLiveStatusJob($liveId, 'closeAgainJob', $closeExecuteAgainTime);
        $this->registerLiveStatusJob($liveId, 'closeSecondJob', $closeExecuteSecondTime);
    }

    private function registerLiveStatusJob($liveId, $jobType, $expression)
    {
        $jobName = $this->makeLiveStatusJobName($liveId, $jobType);
        $this->deleteLiveStatusJob($jobName);

        $job = [
            'name' => $jobName,
            'expression' => $expression,
            'class' => 'Biz\Live\Job\LiveStatusJob',
            'misfire_policy' => 'executing',
            'args' => [
                'liveId' => $liveId,
                'jobType' => $jobType,
            ],
        ];
        $this->getSchedulerService()->register($job);
    }

    private function makeLiveStatusJobName($liveId, $jobType)
    {
        return "LiveStatus_{$jobType}_{$liveId}";
    }

    private function deleteLiveStatusJob($jobName)
    {
        $job = $this->getSchedulerService()->getJobByName($jobName);
        if (!empty($job)) {
            $this->getSchedulerService()->deleteJob($job['id']);
        }
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return LiveCloudStatisticsServiceImpl
     */
    protected function getLiveStatisticsService()
    {
        return $this->getBiz()->service('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return $this->getBiz()->dao('Activity:LiveActivityDao');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
