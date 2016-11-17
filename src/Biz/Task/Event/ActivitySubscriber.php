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
            'activity.finish' => 'onActivityFinish'
        );
    }

    public function onActivityStart(Event $event)
    {
        $activity = $event->getSubject();
        $task = $event->getArgument('task');

        $biz = $this->getBiz();
        $user = $biz['user'];

        $taskResult = $this->getTaskResultService()->getTaskResultByTaskIdAndUserId($task['id'], $user['id']);

        if(!empty($taskResult)){
            return;
        }

        $taskResult = array(
            'activityId' => $activity['id'],
            'courseId'   => $task['courseId'],
            'courseTaskId' => $task['id'],
            'userId'     => $user['id']
        );

        $this->getTaskResultService()->createTaskResult($taskResult);
    }

    public function onActivityFinish(Event $event)
    {
        $activity = $event->getSubject();
        $courseId = $activity['fromCourseId'];

        $taskResults = $this->getTaskResultService()->findUserProgressingTaskByCourseIdAndActivityId($courseId, $activity['id']);

        foreach ($taskResults as $taskResult){
            $this->getTaskService()->taskFinish($taskResult['courseTaskId']);
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
