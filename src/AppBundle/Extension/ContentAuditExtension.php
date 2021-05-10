<?php

namespace AppBundle\Extension;

use Biz\AuditCenter\ContentAuditSources\CommonReview;
use Biz\AuditCenter\ContentAuditSources\CourseNote;
use Biz\AuditCenter\ContentAuditSources\CourseThread;
use Biz\AuditCenter\ContentAuditSources\CourseThreadReply;
use Biz\AuditCenter\ContentAuditSources\GroupThread;
use Biz\AuditCenter\ContentAuditSources\GroupThreadPost;
use Biz\AuditCenter\ContentAuditSources\Thread;
use Biz\AuditCenter\ContentAuditSources\ThreadPostReview;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ContentAuditExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // TODO: Implement register() method.
    }

    /**
     * @return string[]
     *                  简单类暂不使用单例模式
     */
    public function getContentAuditSources()
    {
        return [
            'course_review' => CommonReview::class,
            'course_review_reply' => CommonReview::class,
            'classroom_review' => CommonReview::class,
            'classroom_review_reply' => CommonReview::class,
            'item_bank_exercise_review' => CommonReview::class,
            'item_bank_exercise_review_reply' => CommonReview::class,
            'course_note' => CourseNote::class,
            'group_thread' => GroupThread::class,
            'group_thread_post' => GroupThreadPost::class,
            'course_thread' => CourseThread::class,
            'course_question' => CourseThread::class,
            'course_thread_post' => CourseThreadReply::class,
            'course_question_post' => CourseThreadReply::class,
            'open_course_review' => ThreadPostReview::class,
            'open_course_review_reply' => ThreadPostReview::class,
            'article_review' => ThreadPostReview::class,
            'article_review_reply' => ThreadPostReview::class,
            'classroom_thread_reply' => ThreadPostReview::class,
            'classroom_question_reply' => ThreadPostReview::class,
            'classroom_event_reply' => ThreadPostReview::class,
            'classroom_thread' => Thread::class,
            'classroom_question' => Thread::class,
            'classroom_event' => Thread::class,
        ];
    }
}
