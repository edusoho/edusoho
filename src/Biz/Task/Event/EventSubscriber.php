<?php
namespace Biz\Task\Event;

use Biz\Activity\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'activity.start'  => 'onActivityStart',
            'activity.finish' => 'onActivityFinish'
        );
    }

    public function onActivityStart(Event $event)
    {
    }

    public function onActivityFinish(Event $event)
    {
    }
}
