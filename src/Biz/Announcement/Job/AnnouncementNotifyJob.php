<?php

namespace Biz\Announcement\Job;

use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AnnouncementNotifyJob extends AbstractJob
{
    public function execute()
    {
        $targetId = $this->args['targetId'];
        $targetType = $this->args['targetType'];
        $params = $this->args['params'];
        $targetObject = $params['targetObject'];
        $targetObjectShowUrl = $params['targetObjectShowUrl'];
        $announcement = $params['announcement'];

        $processor = $this->getAnnouncementProcessor($targetType);
        $processor->announcementNotification($targetId, $targetObject, $targetObjectShowUrl, $announcement);
    }

    /**
     * @param  $targetType
     *
     * @return AnnouncementProcessor
     */
    protected function getAnnouncementProcessor($targetType)
    {
        $processor = $this->biz['announcement_processor']->create($targetType);

        return $processor;
    }
}
