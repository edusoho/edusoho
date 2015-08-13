<?php
namespace Topxia\Service\Task\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class TaskEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'course.lesson_start' => 'onLessonStart',
        );
    }

    public function onLessonStart(ServiceEvent $event)
    {
        /*$lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $this->getStatusService()->publishStatus(array(
            'type' => 'start_learn_lesson',
            'courseId' => $course['id'],
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'private' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            ),
        ));*/
    }

    

    protected function getTaskService()
    {
        return ServiceKernel::instance()->createService('Task.TaskService');
    }

    

}
