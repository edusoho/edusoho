<?php

namespace Biz\Announcement\Processor;

use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Queue\Service\QueueService;

class ClassroomAnnouncementProcessor extends AnnouncementProcessor
{
    public function checkManage($targetId)
    {
        return $this->getClassroomService()->canManageClassroom($targetId);
    }

    public function checkTake($targetId)
    {
        return $this->getClassroomService()->canTakeClassroom($targetId);
    }

    public function getTargetShowUrl()
    {
        return 'classroom_show';
    }

    public function announcementNotification($targetId, $targetObject, $targetObjectShowUrl, $announcement)
    {
        $count = $this->getClassroomService()->searchMemberCount(['classroomId' => $targetId, 'role' => 'student']);

        $members = $this->getClassroomService()->searchMembers(
            ['classroomId' => $targetId, 'role' => 'student'],
            ['createdTime' => 'DESC'],
            0, $count
        );

        $result = false;
        if ($members) {
            $this->classroomAnnouncementPush($targetId);
            $message = ['title' => $targetObject['courseSetTitle'].'-'.(empty($targetObject['title']) ? '默认计划' : $targetObject['title']),
                'url' => $targetObjectShowUrl,
                'type' => 'classroom',
                'announcement_id' => $announcement['id'], ];
            foreach ($members as $member) {
                $result = $this->getNotificationService()->notify($member['userId'], 'learn-notice', $message);
            }
        }

        return $result;
    }

    private function classroomAnnouncementPush($targetId)
    {
        if (!$this->isIMEnabled()) {
            return;
        }

        $classroom = $this->getClassroomService()->getClassroom($targetId);

        $conv = $this->getConversationService()->getConversationByTarget($classroom['id'], 'classroom-push');

        $from = [
            'id' => $classroom['id'],
            'type' => 'classroom',
        ];

        $to = [
            'type' => 'classroom',
            'id' => 'all',
            'convNo' => $conv['no'],
        ];

        $body = [
            'type' => 'classroom.announcement.create',
            'classroomId' => $classroom['id'],
            'title' => "《{$classroom['title']}》",
            'message' => "[班级公告] 你正在学习的班级《{$classroom['title']}》有一个新的公告，快去看看吧",
        ];

        $this->createPushJob($from, $to, $body);
    }

    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob([
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ]);

        $this->getQueueService()->pushJob($pushJob);
    }

    public function isIMEnabled()
    {
        $setting = $this->getSettingService()->get('app_im', []);

        if (empty($setting) || empty($setting['enabled'])) {
            return false;
        }

        return true;
    }

    public function tryManageObject($targetId)
    {
        $this->getClassroomService()->tryManageClassroom($targetId);
        $classroom = $this->getClassroomService()->getClassroom($targetId);

        return $classroom;
    }

    public function getTargetObject($targetId)
    {
        return $this->getClassroomService()->getClassroom($targetId);
    }

    public function getActions($action)
    {
        $config = [
            'create' => 'AppBundle:Classroom/Announcement:create',
            'edit' => 'AppBundle:Classroom/Announcement:edit',
            'list' => 'AppBundle:Classroom/Announcement:list',
        ];

        return $config[$action];
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getConversationService()
    {
        return $this->biz->service('IM:ConversationService');
    }
}
