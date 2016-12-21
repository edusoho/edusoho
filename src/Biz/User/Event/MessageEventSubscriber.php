<?php
namespace Biz\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
        );
    }
}
