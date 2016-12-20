<?php
namespace Biz\Announcement\Processor;

use Biz\User\Service\NotificationService;
use Topxia\Service\Common\ServiceKernel;

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
		$count = $this->getClassroomService()->searchMemberCount(array('classroomId'=>$targetId,'role'=>'student'));

    	$members = $this->getClassroomService()->searchMembers(
            array('classroomId'=>$targetId,'role'=>'student'),
            array('createdTime','DESC'),
            0,$count
        );

    	$result = false;
		if ($members) {

			$message = array('title'=> $targetObject['title'],
				'url' => $targetObjectShowUrl,
				'type'=>'classroom');
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