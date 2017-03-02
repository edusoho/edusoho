<?php

namespace Biz\User\Event;

use AppBundle\Common\StringToolkit;
use Biz\User\Service\StatusService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * @return mixed
     */
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.start'  => 'onCourseTaskStart',
            'course.task.finish' => 'onCourseTaskFinish'
        );
    }

    public function onCourseTaskStart(Event $event)
    {
        $taskResult = $event->getSubject();
        $course     = $this->getCourseService()->getCourse($taskResult['courseId']);
        $task       = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        $this->getStatusService()->publishStatus(array(
            'type'       => 'task_start',
            'courseId'   => $course['id'],
            'objectType' => 'task',
            'objectId'   => $task['id'],
            'private'    => $this->isPrivate($course),
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'task'   => $this->simplifyTask($task)
            )
        ));
    }

    public function onCourseTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $course     = $this->getCourseService()->getCourse($taskResult['courseId']);
        $task       = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        $this->getStatusService()->publishStatus(array(
            'type'       => 'task_finish',
            'courseId'   => $course['id'],
            'objectType' => 'task',
            'objectId'   => $task['id'],
            'private'    => $this->isPrivate($course),
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'task'   => $this->simplifyTask($task)
            )
        ));
    }

    protected function simplifyCousrse($course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        return array(
            'id'          => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'title'       => $course['title'],
            'picture'     => $courseSet['cover'],
            'type'        => $course['type'],
            'rating'      => $course['rating'],
            'about'       => StringToolkit::plain($course['summary'], 100),
            'price'       => $course['price']
        );
    }

    protected function simplifyTask($task)
    {
        return array(
            'id'      => $task['id'],
            'number'  => $task['number'],
            'type'    => $task['type'],
            'title'   => $task['title'],
            'summary' => ''
        );
    }

    protected function isPrivate($course)
    {
        $private = $course['status'] == 'published' ? 0 : 1;

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);

            if (array_key_exists('showable', $classroom) && $classroom['showable'] == 1) {
                $private = 0;
            } else {
                $private = 1;
            }
        }

        return $private;
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->getBiz()->service('User:StatusService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
