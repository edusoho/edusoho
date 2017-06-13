<?php

namespace Biz\Task\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\ViewLogService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VideoViewEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'activity.start' => 'onVideoView',
        );
    }

    public function onVideoView(Event $event)
    {
        $activity = $event->getSubject();
        $task = $event->getArgument('task');

        $user = $this->getBiz()->offsetGet('user');
        if ($activity['mediaType'] !== 'video') {
            return false;
        }
        $activityExt = $this->getActivityService()->getActivityConfig($activity['mediaType'])->get($activity['mediaId']);

        $file = $activityExt['file'];

        $taskViewLog = array(
            'courseSetId' => $activity['fromCourseSetId'],
            'courseId' => $activity['fromCourseId'],
            'taskId' => $task['id'],
            'userId' => $user['id'],
            'fileId' => !empty($file['id']) ? $file['id'] : 0,
            'fileType' => !empty($file['type']) ? $file['type'] : 'video',
            'fileStorage' => !empty($file['storage']) ? $file['storage'] : 'net',
            'fileSource' => $activityExt['mediaSource'],
        );

        $this->getTaskViewLogService()->createViewLog($taskViewLog);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return ViewLogService
     */
    protected function getTaskViewLogService()
    {
        return $this->getBiz()->service('Task:ViewLogService');
    }
}
