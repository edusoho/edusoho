<?php

namespace Biz\Live\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LiveStatusSubscriber extends EventSubscriber implements EventSubscriberInterface
{
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
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeJob'));
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeAgainJob'));
        $this->deleteLiveStatusJob($this->makeLiveStatusJobName($liveId, 'closeSecondJob'));
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
        $startExecuteTime = intval($activity['startTime']);
        $closeExecuteTime = intval($activity['startTime'] + $activity['length'] * 60); //预定结束时间询问更新状态
        $closeExecuteAgainTime = intval($activity['startTime'] + $activity['length'] * 60 + 3600); //预定结束时间一个小时后询问更新状态
        $closeExecuteSecondTime = intval($activity['startTime'] + $activity['length'] * 60 + 7200); //预定结束时间两个小时 强制结束直播
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
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }
}
