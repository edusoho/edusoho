<?php

namespace Biz\Course\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LessonEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.lesson.publish' => 'onCourseLessonPublish',
            'course.lesson.unpublish' => 'onCourseLessonUnPublish',
        );
    }

    public function onCourseLessonPublish(Event $event)
    {
        $chapter = $event->getSubject();

        $tasks = $this->getTaskService()->findTasksByChapterId($chapter['id']);

        if (empty($tasks)) {
            return;
        }

        foreach ($tasks as $task) {
            $this->getTaskService()->publishTask($task['id']);
        }
    }

    public function onCourseLessonUnPublish(Event $event)
    {
        $chapter = $event->getSubject();

        $tasks = $this->getTaskService()->findTasksByChapterId($chapter['id']);

        if (empty($tasks)) {
            return;
        }

        foreach ($tasks as $task) {
            $this->getTaskService()->unpublishTask($task['id']);
        }
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
