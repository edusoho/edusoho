<?php
namespace Topxia\Service\Thread\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;

class ThreadEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'thread.delete'     => array('onThreadDelete', 0),
        );
    }

    public function onThreadDelete(ServiceEvent $event)
    {

    }

}