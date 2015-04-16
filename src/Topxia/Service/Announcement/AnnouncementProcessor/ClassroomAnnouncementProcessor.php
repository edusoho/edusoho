<?php
namespace Topxia\Service\Announcement\AnnouncementProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\NumberToolkit;
use Exception;

class ClassroomAnnouncementProcessor implements AnnouncementProcessor
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
		$count = $this->getClassroomService()->searchMemberCount(array('classroomId'=>$targetId,'role'=>'student'));

    	$members = $this->getClassroomService()->searchMembers(
            array('classroomId'=>$targetId,'role'=>'student'),
            array('createdTime','DESC'),
            0,$count
        );

    	$result = false;
		if ($members) {
			foreach ($members as $member) {
        		$result = $this->getNotificationService()->notify($member['userId'], 'default', "【班级公告】你正在学习的<a href='{$targetObjectShowUrl}' target='_blank'>{$targetObject['title']}</a>发布了一个新的公告，<a href='{$targetObjectShowUrl}' target='_blank'>快去看看吧</a>");
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

	public function getShowPageName($targetId)
	{
		$canTake = $this->checkTake($targetId);

		if ($canTake) {
			return 'announcement-show-modal.html.twig';
		} else {
			return 'announcement-classroom-nojoin-show-modal.html.twig';
		}
	}

	protected function getClassroomService()
    {
    	return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }
}