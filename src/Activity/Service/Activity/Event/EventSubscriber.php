<?php
namespace Activity\Service\Activity\Event;

use Topxia\Service\Common\ServiceEvent;
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

    public function onActivityStart(ServiceEvent $event)
    {
    }

    public function onActivityFinish(ServiceEvent $event)
    {
    }
}
