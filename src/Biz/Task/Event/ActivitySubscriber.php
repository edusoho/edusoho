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
            'activity.start' => 'onActivityStart',
            'activity.doing' => 'onActivityDoing',
            'activity.finish' => 'onActivityFinish',
        );
    }

    public function onActivityFinish(Event $event)
    {
        $taskId = $event->getArgument('taskId');

        if ($this->getTaskService()->isFinished($taskId)) {
            $this->getTaskService()->finishTaskResult($taskId);
        }
    }

    public function onActivityStart(Event $event)
    {
        $user = $this->getBiz()->offsetGet('user');
        $task = $event->getArgument('task');
        $this->getTaskService()->startTask($task['id']);
        $this->updateLastLearnTime($task);
    }

    public function updateLastLearnTime($task)
    {
        $user = $this->getBiz()->offsetGet('user');
        $courseMember = $this->getCourseMemberService()->getCourseMember($task['courseId'], $user['id']);
        $this->dispatch('task.show', $courseMember);
    }

    public function onActivityDoing(Event $event)
    {
        $task = $event->getArgument('task');
        $lastTime = $event->getArgument('lastTime');
        $time = time() - $lastTime;
        $learnTimeSec = $this->getTaskService()->getTimeSec('learn');
        if ($time >= $learnTimeSec) {
            $time = $learnTimeSec;
        }

        if (empty($task)) {
            return;
        }
        if ($time > 0) {
            $this->getTaskService()->doTask($task['id'], $time);
        }
        $this->updateLastLearnTime($task);

        if ($this->getTaskService()->isFinished($task['id'])) {
            $this->getTaskService()->finishTaskResult($task['id']);
        }
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

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function dispatch($eventName, $subject)
    {
        $event = new Event($subject);

        return $this->getBiz()->offsetGet('dispatcher')->dispatch($eventName, $event);
    }
}
