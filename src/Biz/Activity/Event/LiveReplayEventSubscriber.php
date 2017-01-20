<?php

namespace Biz\Activity\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LiveReplayEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'live.replay.generate' => 'onLiveReplayGenerate',
        );
    }

    public function onLiveReplayGenerate(Event $event)
    {
        $replays = $event->getSubject();

        if (empty($replays)) {
            return;
        }

        $replay = current($replays);

        if ($replay['type'] != 'open') {
            return;
        }

        $taskId = $replay['lessonId'];

        $task = $this->getTaskService()->getTask($taskId);

        if(empty($task)){
            return;
        }

        $liveActivityFields = array(
            'replayStatus' => 'generated'
        );

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $this->getActivityService()->getActivityConfig('live')->update($activity['id'], $liveActivityFields, $activity);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
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

    protected function getLogger($name)
    {
        $biz = $this->getBiz();
        return $biz['logger'];
    }
}
