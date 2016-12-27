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
            'course.student.create' => 'onStudentNumberChange',
            'course.student.delete' => 'onStudentNumberChange',
            'course.thread.create'  => 'onCourseThreadChange',
            'course.thread.delete'  => 'onCourseThreadChange'
        );
    }

    public function onTaskNumberChange(Event $event)
    {
        $task     = $event->getSubject();
        $courseId = $task['courseId'];
        $this->getCourseService()->updateCourseStatistics($courseId, array(
            'taskNum'
        ));
    }

    public function onStudentNumberChange(Event $event)
    {
        $member = $event->getSubject();
        if ($member['role'] != 'student') {
            return;
        }

        $courseId = $member['courseId'];
        $this->getCourseService()->updateCourseStatistics($courseId, array(
            'studentNum'
        ));
    }

    public function onCourseThreadChange(Event $event)
    {
        $thread = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($thread['courseId'], array(
            'threadNum'
        ));
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }
}
