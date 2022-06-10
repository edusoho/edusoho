<?php

namespace Biz\Activity\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\Live\Service\LiveService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;

class LiveEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'course.teachers.update' => 'onCourseTeachersUpdate',
        ];
    }

    public function onCourseTeachersUpdate(Event $event)
    {
        $course = $event->getSubject();
        $teachers = $event->getArgument('teachers');
        if (empty($teachers)) {
            return;
        }
        $notOverLiveActivities = $this->getActivityService()->search(['fromCourseId' => $course['id'], 'mediaType' => 'live', 'endTime_GT' => time(), 'copyId' => 0], [], 0, PHP_INT_MAX, ['mediaId']);
        if (empty($notOverLiveActivities)) {
            return;
        }
        $liveClient = new EdusohoLiveClient();
        $liveAccount = $liveClient->getLiveAccount();
        if (!$this->getLiveService()->isLiveProviderTeacherRequired($liveAccount['provider'])) {
            return;
        }
        $liveProviderTeacherId = $this->getLiveService()->getLiveProviderTeacherId($teachers[0]['id'], $liveAccount['provider']);
        $notOverLiveActivities = $this->getLiveActivityService()->findLiveActivitiesByIds(array_unique(array_column($notOverLiveActivities, 'mediaId')));
        $liveIds = array_unique(array_column($notOverLiveActivities, 'liveId'));
        foreach ($liveIds as $liveId) {
            $liveClient->updateLive(['liveId' => $liveId, 'teacherId' => $liveProviderTeacherId]);
        }
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
     * @return LiveService
     */
    protected function getLiveService()
    {
        return $this->getBiz()->service('Live:LiveService');
    }
}
