<?php

namespace Biz\Course\Event;

use Biz\Course\Dao\CourseJobDao;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LearningProgressEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'course.task.unpublish' => 'onTaskUnpublish',
            'course.task.publish' => 'onTaskPublish',
            'course.task.update' => 'onTaskUpdate',
            'course.task.delete' => 'onTaskDelete',
        );
    }

    public function onTaskPublish(Event $event)
    {
        $task = $event->getSubject();

        $courseJob = $this->initCourseJobIfNotInit('publishStateIsChange', $task);

        $courseJob['data'][$task['id']]['publishStateIsChange'] += +1;

        $this->getCourseJobDao()->update($courseJob['id'], array('data' => $courseJob['data']));
    }


    public function onTaskUnpublish(Event $event)
    {
        $task = $event->getSubject();

        $courseJob = $this->initCourseJobIfNotInit('publishStateIsChange', $task);

        $courseJob['data'][$task['id']]['publishStateIsChange'] += -1;

        $this->getCourseJobDao()->update($courseJob['id'], array('data' => $courseJob['data']));
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();

        $courseJob = $this->initCourseJobIfNotInit('deleteStateIsChange', $task);

        $courseJob['data'][$task['id']]['delete'] += +1;

        $this->getCourseJobDao()->update($courseJob['id'], array('data' => $courseJob['data']));
    }

    public function onTaskUpdate(Event $event)
    {
        $newTask = $event->getSubject();
        $oldTask = $event->getArguments();
        $isOptionalChange = isset($oldTask['isOptional']) && $newTask['isOptional'] != $oldTask['isOptional'];
        if ($isOptionalChange) {

            $courseJob = $this->initCourseJobIfNotInit('isOptionalStateIsChange', $newTask);

            $courseJob['data'][$newTask['id']]['delete'] += $newTask['isOptional'] == 1 ? +1 : -1;

            $this->getCourseJobDao()->update($courseJob['id'], array('data' => $courseJob['data']));
        }

    }

    private function initCourseJobIfNotInit($type, $task)
    {
        $courseJob = $this->getCourseJobIfNotExistThenCreate($task['courseId']);

        if (!isset($courseJob['data'][$task['id']]) || !isset($courseJob['data'][$task['id']][$type])) {
            $courseJob['data'][$task['id']][$type] = 0;
        }

        return $courseJob;
    }

    private function getCourseJobIfNotExistThenCreate($courseId)
    {
        $courseJob = $this->getCourseJobDao()->getByTypeAndCourseId('refresh_learning_progress', $courseId);

        if (!$courseJob) {
            $courseJob = $this->getCourseJobDao()->create(array('courseId' => $courseId, 'type' => 'refresh_learning_progress'));
        }

        return $courseJob;
    }

    /**
     * @return CourseJobDao
     */
    private function getCourseJobDao()
    {
        return $this->getBiz()->dao('Course:CourseJobDao');
    }
}
