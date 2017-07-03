<?php

namespace Biz\Task\Event;

use Biz\Task\Service\TryViewLogService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseTryViewSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'task.preview' => 'onTaskPreview',
        );
    }

    public function onTaskPreview(Event $event)
    {
        $task = $event->getSubject();
        if (empty($task)) {
            return null;
        }
        $userId = $event->getArgument('userId');

        $tryViewLog = array(
            'userId' => empty($userId) ? 0 : $userId,
            'courseSetId' => $task['fromCourseSetId'],
            'courseId' => $task['courseId'],
            'taskId' => $task['id'],
            'taskType' => $task['type'],
        );

        $this->getTryViewLogService()->createTryViewLog($tryViewLog);
    }

    /**
     * @return TryViewLogService
     */
    protected function getTryViewLogService()
    {
        return $this->getBiz()->service('Task:TryViewLogService');
    }
}
