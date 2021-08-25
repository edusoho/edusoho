<?php

namespace Biz\MultiClass\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MultiClassSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.task.create' => 'onTaskCreate',
            'course.task.update' => 'onTaskUpdate',
            'course.task.delete' => 'onTaskDelete',
            'multi_class.create' => 'onMultiClassCreate',
            'multi_class.group_create' => 'onMultiClassGroupCreate',
            'scrm.user_bind' => 'onUserBind',
        ];
    }

    public function onTaskCreate(Event $event)
    {
        $task = $event->getSubject();
        if ('live' === $task['type']) {
            $this->getMultiClassService()->generateMultiClassTimeRange($task['courseId']);
        }
    }

    public function onTaskUpdate(Event $event)
    {
        $task = $event->getSubject();
        if ('live' === $task['type']) {
            $this->getMultiClassService()->generateMultiClassTimeRange($task['courseId']);
        }
    }

    public function onTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        if ('live' === $task['type']) {
            $this->getMultiClassService()->generateMultiClassTimeRange($task['courseId']);
        }
    }

    public function onMultiClassCreate(Event $event)
    {
        $multiClass = $event->getSubject();

        $this->getSchedulerService()->register([
            'name' => 'GenerateMultiClassRecordJob_'.$multiClass['id'],
            'expression' => time(),
            'class' => 'Biz\MultiClass\Job\GenerateMultiClassRecordJob',
            'misfire_threshold' => 60 * 60,
            'args' => [
                'multiClassId' => $multiClass['id'],
            ],
        ]);
    }

    public function onMultiClassGroupCreate(Event $event)
    {
        $multiClass = $event->getSubject();
        $groups = $event->getArgument('groups');

        $this->getSchedulerService()->register([
            'name' => 'CreateLiveGroupJob_'.$multiClass['id'],
            'expression' => time(),
            'class' => 'Biz\MultiClass\Job\CreateLiveGroupJob',
            'misfire_threshold' => 60 * 60,
            'args' => [
                'multiClassId' => $multiClass['id'],
                'groupIds' => ArrayToolkit::column($groups, 'id'),
            ],
        ]);
    }

    public function onUserBind(Event $event)
    {
        $user = $event->getSubject();
        if (!empty($user['scrmUuid'])) {
            $this->getMultiClassRecordService()->uploadUserRecords($user['id']);
        }
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->getBiz()->service('MultiClass:MultiClassService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return MultiClassRecordService
     */
    protected function getMultiClassRecordService()
    {
        return $this->getBiz()->service('MultiClass:MultiClassRecordService');
    }
}
