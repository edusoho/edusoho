<?php
namespace Topxia\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class StatusEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'status.lesson_start' => 'onLessonStart',
            'status.lesson_finish' => 'onLessonFinish',
            'status.testpaper_finish' => 'onTestpaperFinish',
            'status.homework_finish' => 'onHomeworkFinish',
            'status.post_create' => 'onPostCreate',
            'status.post_delete' => 'onPostDelete',
            'status.post_vote' => 'onPostVote',
        );
    }

    public function onThreadDelete(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onThreadDelete', $event);
    }

    public function onThreadCreate(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onThreadCreate', $event);
    }

    public function onThreadNice(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onThreadNice', $event);
    }

    public function onThreadSticky(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onThreadSticky', $event);
    }

    public function onPostCreate(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onPostCreate', $event);
    }

    public function onPostDelete(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onPostDelete', $event);
    }

    public function onPostVote(ServiceEvent $event)
    {
        $this->callTargetEventProcessor('onPostVote', $event);
    }

    private function callTargetEventProcessor($method, $event)
    {
        $subject = $event->getSubject();

        $processors = ServiceKernel::instance()->getModuleConfig('thread.event_processor');
        if (!isset($processors[$subject['targetType']])) {
            return ;
        }

        $processors = (array) $processors[$subject['targetType']];
        foreach ($processors as $processor) {
            $processor = new $processor();
            if (!method_exists($processor, $method)) {
                break;
            }
            $processor->$method($event);
        }
    }
}
