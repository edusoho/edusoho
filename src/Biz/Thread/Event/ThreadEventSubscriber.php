<?php

namespace Biz\Thread\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThreadEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'thread.delete' => 'onThreadDelete',
            'thread.create' => 'onThreadCreate',
            'thread.nice' => 'onThreadNice',
            'thread.sticky' => 'onThreadSticky',
            'thread.post.create' => 'onPostCreate',
            'thread.post.delete' => 'onPostDelete',
            'thread.post.vote' => 'onPostVote',
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

        $targetType = $subject['targetType'];
        $biz = $this->getBiz();

        if (!isset($biz["thread_event_processor.{$targetType}"])) {
            return;
        }

        $processor = $biz["thread_event_processor.{$targetType}"];

        if (!method_exists($processor, $method)) {
            return;
        }

        $processor->$method($event);
    }

    protected function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }
}
