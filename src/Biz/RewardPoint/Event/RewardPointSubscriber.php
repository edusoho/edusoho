<?php

namespace Biz\RewardPoint\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RewardPointSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.create' => 'onCourseThreadCreate',
            'thread.create' => 'onThreadCreate',
            'course.thread.post.create' => 'onCourseThreadPostCreate',
            'thread.post.create' => 'onThreadPostCreate',
            'course.thread.elite' => 'onCourseThreadElite',
            'thread.nice' => 'onThreadNice',
            'course.review.update' => 'onCourseReviewUpdate',
            'classReview.add' => 'onClassReviewAdd',
        );
    }

    public function onCourseThreadCreate(Event $event)
    {

    }
}