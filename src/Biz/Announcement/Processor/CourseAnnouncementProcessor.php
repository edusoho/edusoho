<?php
namespace Biz\Announcement\Processor;

use Biz\User\Service\NotificationService;
use Topxia\Service\Common\ServiceKernel;

class CourseAnnouncementProcessor extends AnnouncementProcessor
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

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }
}