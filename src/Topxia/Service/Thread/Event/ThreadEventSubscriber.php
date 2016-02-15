<?php
namespace Topxia\Service\Thread\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThreadEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'thread.delete'      => 'onThreadDelete',
            'thread.create'      => 'onThreadCreate',
            'thread.nice'        => 'onThreadNice',
            'thread.sticky'      => 'onThreadSticky',
            'thread.post.create' => 'onPostCreate',
            'thread.post.delete' => 'onPostDelete',
            'thread.post.vote'   => 'onPostVote'
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

    protected function callTargetEventProcessor($method, $event)
    {
        $subject = $event->getSubject();

        $processors = ServiceKernel::instance()->getModuleConfig('thread.event_processor');

        if (!isset($processors[$subject['targetType']])) {
            return;
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

    protected function getThreadService()
    {
        return ServiceKernel::instance()->createService('Thread.ThreadService');
    }
}
