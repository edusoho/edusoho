<?php

namespace Biz\Certificate\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CertificateEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.task.finish' => 'onCourseTaskFinish',
        ];
    }

    public function onCourseTaskFinish(Event $event)
    {
    }
}