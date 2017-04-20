<?php

namespace Biz\Announcement\Processor;

use Topxia\Service\Common\ServiceKernel;
use Biz\User\Service\NotificationService;

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

    public function announcementNotification($targetId, $targetObject, $targetObjectShowUrl)
    {
        $count = $this->getClassroomService()->searchMemberCount(array('classroomId' => $targetId, 'role' => 'student'));

        $members = $this->getClassroomService()->searchMembers(
            array('classroomId' => $targetId, 'role' => 'student'),
            array('createdTime' => 'DESC'),
            0, $count
        );

        $result = false;
        if ($members) {
            $message = array('title' => $targetObject['title'],
                'url' => $targetObjectShowUrl,
                'type' => 'classroom', );
            foreach ($members as $member) {
                $result = $this->getNotificationService()->notify($member['userId'], 'learn-notice', $message);
            }
        }

        return $result;
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
        $config = array(
            'create' => 'AppBundle:Classroom/Announcement:create',
            'edit' => 'AppBundle:Classroom/Announcement:edit',
            'list' => 'AppBundle:Classroom/Announcement:list',
        );

        return $config[$action];
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:ClassroomService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }
}
