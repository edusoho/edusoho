<?php

namespace Biz\Activity\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
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
        return [
            'live.replay.generate' => 'onLiveReplayGenerate',
            'live.replay.delete' => 'onLiveReplayDelete',
        ];
    }

    public function onLiveReplayDelete(Event $event)
    {
        $data = $event->getSubject();
        if (isset($data['lessonId'])) {
            $activity = $this->getActivityService()->getActivity($data['lessonId']);
            $this->getLiveActivityService()->updateLiveActivityWithoutEvent($activity['id'], ['replayStatus' => 'ungenerated']);
        }

        if (isset($data['courseId'])) {
            $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($data['courseId'], 'live');
            foreach ($activities as $activity) {
                $this->getLiveActivityService()->updateLiveActivityWithoutEvent($activity['id'], ['replayStatus' => 'ungenerated']);
            }
        }
    }

    public function onLiveReplayGenerate(Event $event)
    {
        $replays = $event->getSubject();

        if (empty($replays)) {
            return;
        }

        $replay = current($replays);

        if ('live' != $replay['type']) {
            return;
        }

        $activityId = $replay['lessonId'];

        $liveActivityFields = [
            'replayStatus' => LiveReplayService::REPLAY_GENERATE_STATUS,
        ];

        $activity = $this->getActivityService()->getActivity($activityId);
        $this->getActivityService()->updateActivity($activity['id'], $liveActivityFields);
    }

    /**
     * @return LiveActivityService
     */
    private function getLiveActivityService()
    {
        return $this->getBiz()->service('Activity:LiveActivityService');
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
