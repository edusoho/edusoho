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
            'group.thread.create' => 'onGroupThreadCreate',
            'group.thread.update' => 'onGroupThreadUpdate',
            'group.thread.post.create' => 'onGroupThreadPostCreate',
            'group.thread.post.update' => 'onGroupThreadPostUpdate',
            'course.thread.create' => 'onCourseThreadCreate',
            'course.thread.update' => 'onCourseThreadUpdate',
            'course.thread.post.create' => 'onCourseThreadPostCreate',
            'course.thread.post.update' => 'onCourseThreadPostUpdate',
        ];
    }

    public function onCourseThreadPostCreate(Event $event)
    {
        $threadPost = $event->getSubject();
        $thread = $event->getArgument('thread');
        if ('discussion' === $thread['type']) {
            $targetType = 'course_thread_post';
        } elseif ('question' === $thread['type']) {
            $targetType = 'course_question_post';
        } else {
            $targetType = '';
        }
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $this->getContentAuditService()->createAudit([
            'targetType' => $targetType,
            'targetId' => $threadPost['id'],
            'author' => $threadPost['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ]);
    }

    public function onCourseThreadPostUpdate(Event $event)
    {
        $threadPost = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $thread = $event->getArgument('thread');
        if ('discussion' === $thread['type']) {
            $targetType = 'course_thread_post';
        } elseif ('question' === $thread['type']) {
            $targetType = 'course_question_post';
        } else {
            $targetType = '';
        }
        if (empty($sensitiveResult)) {
            return;
        }
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId($targetType, $threadPost['id']);
        if ($existAudit) {
            $this->getContentAuditService()->updateAudit($existAudit['id'], [
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        } else {
            $this->getContentAuditService()->createAudit([
                'targetType' => $targetType,
                'targetId' => $threadPost['id'],
                'author' => $threadPost['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        }
    }

    public function onCourseThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        if ('discussion' === $thread['type']) {
            $targetType = 'course_thread';
        } elseif ('question' === $thread['type']) {
            $targetType = 'course_question';
        } else {
            $targetType = '';
        }
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $this->getContentAuditService()->createAudit([
            'targetType' => $targetType,
            'targetId' => $thread['id'],
            'author' => $thread['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ]);
    }

    public function onCourseThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        if ('discussion' === $thread['type']) {
            $targetType = 'course_thread';
        } elseif ('question' === $thread['type']) {
            $targetType = 'course_question';
        } else {
            $targetType = '';
        }
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId('course_thread', $thread['id']);
        if ($existAudit) {
            $this->getContentAuditService()->updateAudit($existAudit['id'], [
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        } else {
            $this->getContentAuditService()->createAudit([
                'targetType' => $targetType,
                'targetId' => $thread['id'],
                'author' => $thread['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        }
    }

    public function onGroupThreadPostCreate(Event $event)
    {
        $threadPost = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $this->getContentAuditService()->createAudit([
            'targetType' => 'group_thread_post',
            'targetId' => $threadPost['id'],
            'author' => $threadPost['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ]);
    }

    public function onGroupThreadPostUpdate(Event $event)
    {
        $threadPost = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        if (empty($sensitiveResult)) {
            return;
        }
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId('group_thread_post', $threadPost['id']);
        if ($existAudit) {
            $this->getContentAuditService()->updateAudit($existAudit['id'], [
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        } else {
            $this->getContentAuditService()->createAudit([
                'targetType' => 'group_thread_post',
                'targetId' => $threadPost['id'],
                'author' => $threadPost['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        }
    }

    public function onGroupThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $this->getContentAuditService()->createAudit([
            'targetType' => 'group_thread',
            'targetId' => $thread['id'],
            'author' => $thread['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ]);
    }

    public function onGroupThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId('group_thread', $thread['id']);
        if ($existAudit) {
            $this->getContentAuditService()->updateAudit($existAudit['id'], [
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        } else {
            $this->getContentAuditService()->createAudit([
                'targetType' => 'group_thread',
                'targetId' => $thread['id'],
                'author' => $thread['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ]);
        }
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
                'content' => $sensitiveResult['originContent'],
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
