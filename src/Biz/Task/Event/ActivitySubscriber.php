<?php
namespace Biz\Task\Event;


use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Codeages\Biz\Framework\Event\EventSubscriber;

class ActivitySubscriber extends EventSubscriber implements EventSubscriberInterface
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
