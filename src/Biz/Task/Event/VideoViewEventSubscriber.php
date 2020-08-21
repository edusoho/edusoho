<?php

namespace Biz\Task\Event;

use Biz\Activity\Service\ActivityService;
use Biz\File\Service\UploadFileService;
use Biz\Task\Service\ViewLogService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VideoViewEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'activity.start' => 'onVideoView',
        ];
    }

    public function onVideoView(Event $event)
    {
        $activity = $event->getSubject();
        $task = $event->getArgument('task');

        $user = $this->getBiz()->offsetGet('user');
        if ('video' !== $activity['mediaType']) {
            return false;
        }

        $content = json_decode(json_decode($activity['content'], true), true);
        if (!empty($content['id'])) {
            $file = $this->getUploadFileService()->getFile($content['id']);
        }

        $taskViewLog = [
            'courseSetId' => $activity['fromCourseSetId'],
            'courseId' => $activity['fromCourseId'],
            'taskId' => $task['id'],
            'userId' => $user['id'],
            'fileId' => !empty($file['id']) ? $file['id'] : 0,
            'fileType' => !empty($file['type']) ? $file['type'] : 'video',
            'fileStorage' => !empty($file['storage']) ? $file['storage'] : 'net',
            'fileSource' => !empty($content['source']) ? $content['source'] : 'self',
        ];

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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
