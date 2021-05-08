<?php

namespace AppBundle\Extension;

use Biz\AuditCenter\ReportSources\CommonReview;
use Biz\AuditCenter\ReportSources\CourseNote;
use Biz\AuditCenter\ReportSources\CourseThread;
use Biz\AuditCenter\ReportSources\CourseThreadReply;
use Biz\AuditCenter\ReportSources\GroupThread;
use Biz\AuditCenter\ReportSources\GroupThreadPost;
use Biz\AuditCenter\ReportSources\Thread;
use Biz\AuditCenter\ReportSources\ThreadPostReview;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ReportExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // TODO: Implement register() method.
    }

    /**
     * @return string[]
     *                  简单类暂不使用单例模式
     */
    public function getReportSources()
    {
        return [
            'course_review' => CommonReview::class,
            'course_review_reply' => CommonReview::class,
            'classroom_review' => CommonReview::class,
            'classroom_review_reply' => CommonReview::class,
            'course_note' => CourseNote::class,
            'item_bank_exercise_review' => CommonReview::class,
            'item_bank_exercise_review_reply' => CommonReview::class,
            'course_thread' => CourseThread::class,
            'course_question' => CourseThread::class,
            'course_thread_reply' => CourseThreadReply::class,
            'course_question_reply' => CourseThreadReply::class,
            'article_review' => ThreadPostReview::class,
            'open_course_review' => ThreadPostReview::class,
            'classroom_thread' => Thread::class,
            'classroom_question' => Thread::class,
            'classroom_event' => Thread::class,
            'classroom_question_reply' => ThreadPostReview::class,
            'classroom_thread_reply' => ThreadPostReview::class,
            'classroom_event_reply' => ThreadPostReview::class,
            'group_thread' => GroupThread::class,
            'group_thread_reply' => GroupThreadPost::class,
        ];
    }
}
