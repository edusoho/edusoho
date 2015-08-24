<?php
namespace Topxia\Service\Task\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Task\TaskProcessor\TaskProcessorFactory;

class TaskEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'task.finished' => 'onFinished',
        );
    }

    public function onFinished(ServiceEvent $event)
    {
        $targetObject = $event->getSubject();
        $taskType = $event->getArgument('taskType');
        $userId = $event->getArgument('userId');

        $taskProcessor = $this->getTaskProcessor($taskType);

        $taskProcessor->finishTask($targetObject, $userId);
    }

    

    protected function getTaskProcessor($type)
    {
        return TaskProcessorFactory::create($type);
    }

    

}
