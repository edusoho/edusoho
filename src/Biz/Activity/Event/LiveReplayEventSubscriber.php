<?php

namespace Biz\Activity\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\LiveReplayService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
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

        if ($replay['type'] != 'live') {
            return;
        }

        $activityId = $replay['lessonId'];

        $liveActivityFields = array(
            'replayStatus' => LiveReplayService::REPLAY_GENERATE_STATUS,
        );

        $activity = $this->getActivityService()->getActivity($activityId);
        $this->getActivityService()->updateActivity($activity['id'], $liveActivityFields);
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
