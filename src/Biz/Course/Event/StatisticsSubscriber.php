<?php

namespace Biz\Course\Event;

use Biz\Task\Service\TaskService;
use Biz\System\Service\LogService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatisticsSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.create'    => 'onTaskNumberChange',
            'course.task.delete'    => 'onTaskNumberChange',
            'course.task.publish'   => 'onPublishTaskNumberChange',
            'course.task.unpublish' => 'onPublishTaskNumberChange',

            'course.thread.create' => 'onCourseThreadChange',
            'course.thread.delete' => 'onCourseThreadChange',

            'course.review.add'    => 'onReviewNumberChange',
            'course.review.update' => 'onReviewNumberChange',
            'course.review.delete' => 'onReviewNumberChange'
        );
    }

    public function onTaskNumberChange(Event $event)
    {
        $task = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($task['courseId'], array(
            'taskNum'
        ));
    }

    public function onPublishTaskNumberChange(Event $event)
    {
        $task = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($task['courseId'], array(
            'publishedTaskNum'
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

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
