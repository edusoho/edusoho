<?php
namespace Topxia\Service\Thread\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\Event;
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

    public function onThreadDelete(Event $event)
    {
        $this->callTargetEventProcessor('onThreadDelete', $event);
    }

    public function onThreadCreate(Event $event)
    {
        $this->callTargetEventProcessor('onThreadCreate', $event);
    }

    public function onThreadNice(Event $event)
    {
        $this->callTargetEventProcessor('onThreadNice', $event);
    }

    public function onThreadSticky(Event $event)
    {
        $this->callTargetEventProcessor('onThreadSticky', $event);
    }

    public function onPostCreate(Event $event)
    {
        $this->callTargetEventProcessor('onPostCreate', $event);
    }

    public function onPostDelete(Event $event)
    {
        $this->callTargetEventProcessor('onPostDelete', $event);
    }

    public function onPostVote(Event $event)
    {
        $this->callTargetEventProcessor('onPostVote', $event);
    }

    protected function callTargetEventProcessor($method, Event $event)
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
        return ServiceKernel::instance()->createService('Thread:ThreadService');
    }
}
