<?php
namespace Biz\Task\Event;


use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActivitySubscriber extends EventSubscriber  implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'activity.start'  => 'onActivityStart',
            'activity.doing'  => 'onActivityDoing',
            'activity.finish' => 'onActivityFinish'
        );
    }

    public function onActivityStart(Event $event)
    {
        $task = $event->getArgument('task');

        $this->getTaskService()->startTask($task['id']);
    }

    public function onActivityDoing(Event $event)
    {
        $taskId = $event->getArgument('taskId');

        if(!$event->hasArgument('timeStep')){
            $time = TaskService::LEARN_TIME_STEP;
        }else{
            $time = $event->getArgument('timeStep');
        }

        if(empty($taskId)){
            return;
        }

        $this->getTaskService()->doingTask($taskId, $time);
    }

    public function onActivityFinish(Event $event)
    {
        $activity = $event->getSubject();

        $taskResults = $this->getTaskResultService()->findUserProgressingTaskResultByActivityId($activity['id']);

        foreach ($taskResults as $taskResult){
            $this->getTaskService()->finishTask($taskResult['courseTaskId']);
        }
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

}
