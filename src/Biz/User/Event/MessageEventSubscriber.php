<?php
namespace Biz\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Codeages\PluginBundle\Event\EventSubscriber;

class MessageEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
        );
    }
}
