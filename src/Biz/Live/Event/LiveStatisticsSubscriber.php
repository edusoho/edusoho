<?php

namespace Biz\Live\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class LiveStatisticsSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'live.activity.create' => 'onLiveActivityCreate',
            'live.activity.update' => 'onLiveActivityUpdate',
            'live.activity.delete' => 'onLiveActivityDelete',
        );
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

        $job = array(
            'name' => 'LiveStatisticsNextDay_'.$liveId,
            'expression' => intval($activity['startTime'] + $activity['length'] * 60 + 86400),
            'class' => 'Biz\Live\Job\LiveStatisticsJob',
            'misfire_policy' => 'executing',
            'args' => array(
                'liveId' => $liveId,
            ),
        );
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
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->dao('Activity:ActivityService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        $biz = $this->getBiz();

        return $biz['dispatcher']->dispatch($eventName, $event);
    }
}
