<?php

namespace Biz\Course\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatisticsSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.create'    => 'onTaskNumberChange',
            'course.task.delete'    => 'onTaskNumberChange',

            'course.thread.create'  => 'onCourseThreadChange',
            'course.thread.delete'  => 'onCourseThreadChange',

            'course.review.add'     => 'onReviewNumberChange',
            'course.review.update'  => 'onReviewNumberChange',
            'course.review.delete'  => 'onReviewNumberChange'
        );
    }

    public function onTaskNumberChange(Event $event)
    {
        $task = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($task['courseId'], array(
            'taskNum'
        ));
    }

    public function onCourseThreadChange(Event $event)
    {
        $thread = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($thread['courseId'], array(
            'threadNum'
        ));
    }

    public function onReviewNumberChange(Event $event)
    {
        $review = $event->getSubject();

        $this->getCourseService()->updateCourseStatistics($review['courseId'], array(
            'ratingNum'
        ));
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getReviewService()
    {
        return $this->getBiz()->service('Course:ReviewService');
    }

    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }
}
