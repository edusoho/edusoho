<?php
namespace Topxia\Service\Announcement\AnnouncementProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\NumberToolkit;
use Exception;

class CourseAnnouncementProcessor implements AnnouncementProcessor
{
	public function checkManage($targetId)
	{
		return $this->getCourseService()->canManageCourse($targetId);
	}

    public function checkTake($targetId){
    	return $this->getCourseService()->canTakeCourse($targetId);
    }

    public function getTargetShowUrl()
    {
    	return 'course_show';
    }

	public function announcementNotification($targetId, $targetObject, $targetObjectShowUrl)
	{
		$count = $this->getCourseService()->getCourseStudentCount($targetId);
    	$members = $this->getCourseService()->findCourseStudents($targetId, 0, $count);

    	$result = false;
		if ($members) {
				$message = array('title'=> $targetObject['title'],
				'url' => $targetObjectShowUrl,
				'type'=>'course');
			foreach ($members as $member) {
        		$result = $this->getNotificationService()->notify($member['userId'], 'learn-notice', $message);
    		}
		}
		
		return $result;
	}

	public function tryManageObject($targetId)
	{
		$course = $this->getCourseService()->tryManageCourse($targetId);

        return $course;
	}

	public function getTargetObject($targetId)
	{
		return $this->getCourseService()->getCourse($targetId);
	}

	public function getShowPageName($targetId)
	{
		$canTake = $this->checkTake($targetId);

		if ($canTake) {
			return 'announcement-show-modal.html.twig';
		} else {
			return 'announcement-course-nojoin-show-modal.html.twig';
		}
	}

	protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User.NotificationService');
    }
}