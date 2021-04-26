<?php

namespace Biz\AuditCenter\Event;

use Biz\AuditCenter\Service\ContentAuditService;
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
        $note = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $this->getContentAuditService()->createAudit([
            'targetType' => 'course_note',
            'targetId' => $note['id'],
            'author' => $note['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ]);
    }

    public function onCourseNoteUpdate(Event $event)
    {
        $note = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId('course_note', $note['id']);
        if ($existAudit) {
            $this->getContentAuditService()->updateAudit($existAudit['id'], [
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        } else {
            $this->getContentAuditService()->createAudit([
                'targetType' => 'course_note',
                'targetId' => $note['id'],
                'author' => $note['userId'],
                'content' => $note['content'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        }
    }

    /**
     * @return ContentAuditService
     */
    public function getContentAuditService()
    {
        return $this->getBiz()->service('AuditCenter:ContentAuditService');
    }
}
