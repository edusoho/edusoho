<?php

namespace Biz\Course\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChapterEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.chapter.publish' => 'onCourseChpaterPublish',
            'course.chapter.unpublish' => 'onCourseChpaterUnPublish',
        );
    }

    public function onCourseChpaterPublish(Event $event)
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

    public function onCourseChpaterUnPublish(Event $event)
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }
}
