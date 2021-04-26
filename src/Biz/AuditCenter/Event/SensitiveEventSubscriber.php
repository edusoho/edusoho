<?php

namespace Biz\AuditCenter\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SensitiveEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.note.create' => 'onCourseNoteCreate',
            'course.note.update' => 'onCourseNoteUpdate',
        ];
    }

    public function onCourseNoteCreate(Event $event)
    {
    }

    public function onCourseNoteUpdate(Event $event)
    {
    }
}
