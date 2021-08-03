<?php

namespace Biz\MultiClass\Event;

use Biz\MultiClass\Service\MultiClassService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MultiClassSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'live.activity.create' => 'onLiveActivityCreate',
            'live.activity.update' => 'onLiveActivityUpdate',
            'course.task.delete' => 'onTaskDelete',
        ];
    }

    public function onLiveActivityCreate(Event $event)
    {
        $activity = $event->getArgument('activity');
        $this->getMultiClassService()->generateMultiClassTimeRange($activity['fromCourseId']);
    }

    public function onLiveActivityUpdate(Event $event)
    {
        $activity = $event->getArgument('activity');
        $this->getMultiClassService()->generateMultiClassTimeRange($activity['fromCourseId']);
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        if ('live' === $task['type']) {
            $this->getMultiClassService()->generateMultiClassTimeRange($task['courseId']);
        }
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->getBiz()->service('MultiClass:MultiClassService');
    }
}
