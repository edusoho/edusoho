<?php


namespace Codeages\PluginBundle\Event;


use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'test' => 'testMethod'
        );
    }

    public function testMethod(Event $event)
    {

    }
}