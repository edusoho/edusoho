<?php

namespace Biz\AuditCenter\Event;

use Biz\AuditCenter\ContentAuditSources\AbstractSource;
use Biz\AuditCenter\Service\ContentAuditService;
use Biz\Goods\Service\GoodsService;
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
            'review.create' => 'onReviewCreate',
            'review.update' => 'onReviewUpdate',
            'thread.post.create' => 'onThreadPostCreate',
            'thread.create' => 'onThreadCreate',
            'thread.update' => 'onThreadUpdate',
        ];
    }

    public function onThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $threadType = $this->getThreadTargetType($thread);
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId($threadType, $thread['id']);
        if ($existAudit) {
            $audit = $this->getContentAuditService()->updateAudit($existAudit['id'], array_merge([
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        } else {
            $audit = $this->getContentAuditService()->createAudit(array_merge([
                'targetType' => $threadType,
                'targetId' => $thread['id'],
                'author' => $thread['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        }
        $source = $this->getContentAuditSource($audit['targetType']);

        if ($source) {
            $source->handleSource($audit);
        }
    }

    protected function checkContent($content, $sensitiveWords)
    {
        $auditSetting = $this->getContentAuditService()->getAuditSetting();
        if (empty($auditSetting['enable_auto_audit'])) {
            $status = 'none_checked';
            $auditor = 0;
        } else {
            if (!empty($sensitiveWords)) {
                $auditor = 0;
                $status = 'none_checked';
            } else {
                $auditor = -1;
                $status = 'pass';
            }
        }

        return [
            'status' => $status,
            'auditor' => $auditor,
        ];
    }

    public function onThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');

        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => $this->getThreadTargetType($thread),
            'targetId' => $thread['id'],
            'author' => $thread['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    public function getThreadTargetType($thread)
    {
        if ('classroom' === $thread['targetType']) {
            if ('discussion' === $thread['type']) {
                $threadTargetType = 'classroom_thread';
            } elseif ('question' === $thread['type']) {
                $threadTargetType = 'classroom_question';
            } elseif ('event' === $thread['type']) {
                $threadTargetType = 'classroom_event';
            } else {
                $threadTargetType = '';
            }
        } else {
            $threadTargetType = '';
        }

        return $threadTargetType;
    }

    public function onThreadPostCreate(Event $event)
    {
        $threadPost = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $thread = $event->getArgument('thread');
        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => $this->getThreadPostTargetType($threadPost, $thread),
            'targetId' => $threadPost['id'],
            'author' => $threadPost['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    private function getThreadPostTargetType($threadPost, $thread)
    {
        if ('openCourse' === $threadPost['targetType']) {
            $threadPostTargetType = empty($threadPost['parentId']) ? 'open_course_review' : 'open_course_review_reply';
        } elseif ('article' === $threadPost['targetType']) {
            $threadPostTargetType = empty($threadPost['parentId']) ? 'article_review' : 'article_review_reply';
        } elseif ('classroom' === $threadPost['targetType']) {
            if ($thread) {
                if ('discussion' === $thread['type']) {
                    $threadPostTargetType = 'classroom_thread_reply';
                } elseif ('question' === $thread['type']) {
                    $threadPostTargetType = 'classroom_question_reply';
                } elseif ('event' === $thread['type']) {
                    $threadPostTargetType = 'classroom_event_reply';
                } else {
                    $threadPostTargetType = '';
                }
            } else {
                $threadPostTargetType = '';
            }
        } else {
            $threadPostTargetType = '';
        }

        return $threadPostTargetType;
    }

    public function onReviewCreate(Event $event)
    {
        $review = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');

        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => $this->getReviewAuditTargetType($review),
            'targetId' => $review['id'],
            'author' => $review['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    public function onReviewUpdate(Event $event)
    {
        $review = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $reviewAuditTargetType = $this->getReviewAuditTargetType($review);

        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId($reviewAuditTargetType, $review['id']);
        if ($existAudit) {
            $audit = $this->getContentAuditService()->updateAudit($existAudit['id'], array_merge([
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        } else {
            $audit = $this->getContentAuditService()->createAudit(array_merge([
                'targetType' => $reviewAuditTargetType,
                'targetId' => $review['id'],
                'author' => $review['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        }

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    private function getReviewAuditTargetType($review)
    {
        if ('goods' === $review['targetType']) {
            $goods = $this->getGoodsService()->getGoods($review['targetId']);
            if ('course' === $goods['type']) {
                $reviewTargetType = empty($review['parentId']) ? 'course_review' : 'course_review_reply';
            } elseif ('classroom' === $goods['type']) {
                $reviewTargetType = empty($review['parentId']) ? 'classroom_review' : 'classroom_review_reply';
            } else {
                $reviewTargetType = '';
            }
        } elseif ('course' === $review['targetType']) {
            $reviewTargetType = empty($review['parentId']) ? 'course_review' : 'course_review_reply';
        } elseif ('item_bank_exercise' === $review['targetType']) {
            $reviewTargetType = empty($review['parentId']) ? 'item_bank_exercise_review' : 'item_bank_exercise_review_reply';
        } else {
            $reviewTargetType = '';
        }

        return $reviewTargetType;
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
        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => $targetType,
            'targetId' => $threadPost['id'],
            'author' => $threadPost['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);

        if ($source) {
            $source->handleSource($audit);
        }
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
            $audit = $this->getContentAuditService()->updateAudit($existAudit['id'], array_merge([
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        } else {
            $audit = $this->getContentAuditService()->createAudit(array_merge([
                'targetType' => $targetType,
                'targetId' => $threadPost['id'],
                'author' => $threadPost['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        }
        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
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
        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => $targetType,
            'targetId' => $thread['id'],
            'author' => $thread['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
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
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId($targetType, $thread['id']);
        if ($existAudit) {
            $audit = $this->getContentAuditService()->updateAudit($existAudit['id'], array_merge([
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        } else {
            $audit = $this->getContentAuditService()->createAudit(array_merge([
                'targetType' => $targetType,
                'targetId' => $thread['id'],
                'author' => $thread['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        }
        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    public function onGroupThreadPostCreate(Event $event)
    {
        $threadPost = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => 'group_thread_post',
            'targetId' => $threadPost['id'],
            'author' => $threadPost['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
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
            $audit = $this->getContentAuditService()->updateAudit($existAudit['id'], array_merge([
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        } else {
            $audit = $this->getContentAuditService()->createAudit(array_merge([
                'targetType' => 'group_thread_post',
                'targetId' => $threadPost['id'],
                'author' => $threadPost['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        }
        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    public function onGroupThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => 'group_thread',
            'targetId' => $thread['id'],
            'author' => $thread['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    public function onGroupThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId('group_thread', $thread['id']);
        if ($existAudit) {
            $audit = $this->getContentAuditService()->updateAudit($existAudit['id'], array_merge([
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        } else {
            $audit = $this->getContentAuditService()->createAudit(array_merge([
                'targetType' => 'group_thread',
                'targetId' => $thread['id'],
                'author' => $thread['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        }
        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    public function onCourseNoteCreate(Event $event)
    {
        $note = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $audit = $this->getContentAuditService()->createAudit(array_merge([
            'targetType' => 'course_note',
            'targetId' => $note['id'],
            'author' => $note['userId'],
            'content' => $sensitiveResult['originContent'],
            'sensitiveWords' => $sensitiveResult['keywords'],
        ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    public function onCourseNoteUpdate(Event $event)
    {
        $note = $event->getSubject();
        $sensitiveResult = $event->getArgument('sensitiveResult');
        $existAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId('course_note', $note['id']);
        if ($existAudit) {
            $audit = $this->getContentAuditService()->updateAudit($existAudit['id'], array_merge([
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        } else {
            $audit = $this->getContentAuditService()->createAudit(array_merge([
                'targetType' => 'course_note',
                'targetId' => $note['id'],
                'author' => $note['userId'],
                'content' => $sensitiveResult['originContent'],
                'sensitiveWords' => $sensitiveResult['keywords'],
            ], $this->checkContent($sensitiveResult['originContent'], $sensitiveResult['keywords'])));
        }

        $source = $this->getContentAuditSource($audit['targetType']);
        if ($source) {
            $source->handleSource($audit);
        }
    }

    /**
     * @return ContentAuditService
     */
    public function getContentAuditService()
    {
        return $this->getBiz()->service('AuditCenter:ContentAuditService');
    }

    /**
     * @return GoodsService
     */
    public function getGoodsService()
    {
        return $this->getBiz()->service('Goods:GoodsService');
    }

    /**
     * @param $targetType
     *
     * @return AbstractSource
     */
    private function getContentAuditSource($targetType)
    {
        global $kernel;
        $reportSources = $kernel->getContainer()->get('extension.manager')->getContentAuditSources();

        if (empty($reportSources[$targetType])) {
            return null;
        }

        return new $reportSources[$targetType]($this->getBiz());
    }
}
