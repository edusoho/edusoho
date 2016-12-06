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
            'course.student.delete' => 'onStudentNumberChange'
        );
    }

    /* update task count in course when task created or deleted */
    public function onTaskNumberChange(Event $event)
    {
        $task     = $event->getSubject();
        $courseId = $task['courseId'];
        $tasks    = $this->getTaskService()->findTasksByCourseId($courseId);
        $this->getCourseService()->updateCourseStatistics($courseId, array(
            'taskCount' => count($tasks)
        ));
    }

    /* update student count in course when student added to course or deleted from course */
    public function onStudentNumberChange(Event $event)
    {
        $member = $event->getSubject();
        if ($member['role'] != 'student') {
            return;
        }

        $courseId = $member['courseId'];
        $students = $this->getCourseService()->findStudentsByCourseId($courseId);
        $this->getCourseService()->updateCourseStatistics($courseId, array(
            'studentCount' => count($students)
        ));
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
