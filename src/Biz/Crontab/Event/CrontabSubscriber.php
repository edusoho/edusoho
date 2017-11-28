<?php

namespace  Biz\Crontab\Event;

use Biz\Crontab\Service\CrontabService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Codeages\Biz\Framework\Event\Event;

class CrontabSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'scheduler.job.created' => 'onSchedulerJobCreated',
            'scheduler.job.executing' => 'onSchedulerJobExecuting',
        );
    }

    public function onSchedulerJobCreated(Event $event)
    {
        $job = $event->getSubject();
        if (!empty($job['next_fire_time'])) {
            $this->getCrontabService()->setNextExcutedTime($job['next_fire_time']);
        }
    }

    public function onSchedulerJobExecuting(Event $event)
    {
        $jobFired = $event->getSubject();
        $job = $jobFired['job_detail'];
        if (!empty($job['next_fire_time'])) {
            $this->getCrontabService()->setNextExcutedTime($job['next_fire_time']);
        }
    }

    /**
     * @return CrontabService
     */
    protected function getCrontabService()
    {
        return $this->getBiz()->service('Crontab:CrontabService');
    }
}
