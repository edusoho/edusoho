<?php
namespace Topxia\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;

class PointEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.registered' => 'userRegistered',
        );
    }

    public function userRegistered(ServiceEvent $event)
    {

    }

}