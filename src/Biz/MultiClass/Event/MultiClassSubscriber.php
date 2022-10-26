<?php

namespace Biz\MultiClass\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\LiveActivityService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Util\EsLiveClient;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class MultiClassSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'course.task.create' => 'onTaskCreate',
            'course.task.update' => 'onTaskUpdate',
            'course.task.delete' => 'onTaskDelete',
            'live.activity.create' => 'onLiveActivityCreate',
            'multi_class.create' => 'onMultiClassCreate',
            'multi_class.group_create' => 'onMultiClassGroupCreate',
            'multi_class.group_delete' => 'onMultiClassGroupDelete',
            'multi_class.group_batch_delete' => 'onMultiClassGroupBatchDelete',
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

    public function onLiveActivityCreate(Event $event)
    {
        $activity = $event->getArgument('activity');
        $live = $event->getArgument('live');
        $multiClass = $this->getMultiClassService()->getMultiClassByCourseId($activity['fromCourseId']);
        if (!empty($multiClass['bundle_no'])) {
            $this->getEsLiveClient()->batchUpdateRoomMemberGroupBundle([$live['roomId']], $multiClass['bundle_no']);
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
        if (empty($multiClass['bundle_no'])) {
            $bundle = $this->getEsLiveClient()->createMemberGroupBundle($multiClass['title']);
            if (!empty($bundle['no'])) {
                $multiClass = $this->getMultiClassService()->updateMultiClassBundleNo($multiClass['id'], $bundle['no']);
            } else {
                throw MultiClassException::CREATE_GROUP_FAILED();
            }
        }
        $activities = $this->getActivityService()->search(['fromCourseId' => $multiClass['courseId'], 'mediaType' => 'live', 'endTime_GT' => time()], [], 0, PHP_INT_MAX, ['mediaId']);
        $liveActivities = $this->getLiveActivityService()->findLiveActivitiesByIds(array_column($activities, 'mediaId'));
        $roomIds = array_filter(array_column($liveActivities, 'roomId'));
        if ($roomIds) {
            $this->getEsLiveClient()->batchUpdateRoomMemberGroupBundle($roomIds, $multiClass['bundle_no']);
        }
        if (empty($groups)) {
            return;
        }
        $liveGroups = $this->getEsLiveClient()->batchCreateMemberGroup($multiClass['bundle_no'], array_column($groups, 'name'));
        if (empty($liveGroups)) {
            throw MultiClassException::CREATE_GROUP_FAILED();
        }
        $liveGroups = array_column($liveGroups, null, 'name');
        $createLiveGroups = [];
        foreach ($groups as $group) {
            $createLiveGroups[] = [
                'group_id' => $group['id'],
                'live_code' => $liveGroups[$group['name']]['no'],
            ];
        }
        $this->getMultiClassGroupService()->batchCreateLiveGroups($createLiveGroups);
    }

    public function onMultiClassGroupDelete(Event $event)
    {
        $liveGroup = $event->getSubject();
        $this->getEsLiveClient()->deleteMemberGroup($liveGroup['live_code']);
    }

    public function onMultiClassGroupBatchDelete(Event $event)
    {
        $liveGroups = $event->getSubject();
        $this->getEsLiveClient()->batchDeleteMemberGroups(array_column($liveGroups, 'live_code'));
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
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->getBiz()->service('MultiClass:MultiClassGroupService');
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

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->getBiz()->service('Activity:LiveActivityService');
    }

    protected function getEsLiveClient()
    {
        return new EsLiveClient();
    }
}
