<?php

namespace Biz\Live\Event;

use Biz\Course\Event\CourseSyncSubscriber;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class LiveStatisticsSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'live.activity.create' => 'onLiveActivityCreate',
            'live.activity.update' => 'onLiveActivityUpdate',
            'live.activity.delete' => 'onLiveActivityDelete',
        ];
    }

    public function onLiveActivityCreate(Event $event)
    {
        $this->registerLiveStatisticsJob($event->getSubject(), $event->getArgument('activity'));
    }

    public function onLiveActivityUpdate(Event $event)
    {
        $this->registerLiveStatisticsJob($event->getArgument('liveId'), $event->getArgument('activity'));
    }

    public function onLiveActivityDelete(Event $event)
    {
        $this->deleteLiveStatisticsJob($event->getSubject());
    }

    private function registerLiveStatisticsJob($liveId, $activity)
    {
        $this->deleteLiveStatisticsJob($liveId);

        $job = [
            'name' => 'LiveStatisticsNextDay_'.$liveId,
            'expression' => intval($activity['startTime'] + $activity['length'] * 60 + 86400),
            'class' => 'Biz\Live\Job\LiveStatisticsJob',
            'misfire_policy' => 'executing',
            'args' => [
                'liveId' => $liveId,
            ],
        ];
        $this->getSchedulerService()->register($job);
    }

    private function deleteLiveStatisticsJob($liveId)
    {
        $job = $this->getSchedulerService()->getJobByName('LiveStatisticsNextDay_'.$liveId);
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
