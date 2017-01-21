<?php
namespace Biz\Task\Event;

use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActivitySubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'activity.start'    => 'onActivityStart',
            'activity.doing'    => 'onActivityDoing',
            'activity.watching' => 'onActivityWatching',
            'activity.operated' => 'onActivityOperated'
        );
    }

    public function onActivityOperated(Event $event)
    {
        if (!$event->hasArgument('taskId')) {
            return;
        }

        $taskId = $event->getArgument('taskId');

        if ($this->getTaskService()->isFinished($taskId)) {
            $this->getTaskService()->finishTaskResult($taskId);
        }
    }

    public function onActivityWatching(Event $event)
    {
        $taskId = $event->getArgument('taskId');

        if (!$event->hasArgument('watchTime') || $event->hasArgument('watchTime') >= TaskService::WATCH_TIME_STEP) {
            $watchTime = TaskService::WATCH_TIME_STEP;
        } else {
            $watchTime = $event->getArgument('watchTime');
        }

        if (empty($taskId)) {
            return;
        }

        $this->getTaskService()->watchTask($taskId, $watchTime);
    }

    public function onActivityStart(Event $event)
    {
        $task = $event->getArgument('task');
        $this->getTaskService()->startTask($task['id']);
    }

    public function onActivityDoing(Event $event)
    {
        $taskId = $event->getArgument('taskId');

        if (!$event->hasArgument('timeStep')) {
            $time = TaskService::LEARN_TIME_STEP;
        } else {
            $time = $event->getArgument('timeStep');
        }

        if (empty($taskId)) {
            return;
        }

        $this->getTaskService()->doTask($taskId, $time);
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }
}
