<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;


class AnnouncementController extends BaseController
{

	public function showAction(Request $request, $id, $targetId)
	{
		//list($course, $member) = $this->getCourseService()->tryTakeCourse($targetId);
        $announcement = $this->getAnnouncementService()->getAnnouncement($targetId, $id);

        $classroom = array();
        $canLook = false;
        if ($announcement['targetType'] == 'classroom') {
        	$classroom = $this->getClassroomService()->getClassroom($targetId);
        	$canLook = $this->getClassroomService()->canLookClassroom($targetId);
        	
        	if(!$canLook){
        		return $this->render('TopxiaWebBundle:Announcement:announcement-classroom-nojoin-show-modal.html.twig',array(
					'announcement' => $announcement,
					'canLook' => $canLook,
					'classroom' => $classroom,
				));
        	}
        }
        

		return $this->render('TopxiaWebBundle:Announcement:announcement-show-modal.html.twig',array(
			'announcement' => $announcement,
			//'course' => $course,
			'canLook' => $canLook,
			'classroom' => $classroom,
			//'canManage' => $this->getCourseService()->canManageCourse($course['id']),
		));
	}

	public function showAllAction(Request $request, $targetType, $targetId)
	{
		$conditions = array(
			'targetType' => $targetType,
			'targetId' => $targetId
		);

		$announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime','DESC'), 0, 10000);
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($announcements, 'userId'));

		return $this->render('TopxiaWebBundle:Announcement:announcement-show-all-modal.html.twig',array(
			'announcements'=>$announcements,
			'users'=>$users
		));
	}

	public function createAction(Request $request, $targetType, $targetId)
	{
		$targetObject = $this->checkPermission($targetType, $targetId);
		
	    if($request->getMethod() == 'POST'){
        	$announcement = $this->getAnnouncementService()->createAnnouncement($targetType, $targetId, $request->request->all());

        	if ($request->request->get('notify') == 'notify'){
	        	$result = $this->announcementNotification($targetType, $targetId, $targetObject);
	        }

        	return $this->createJsonResponse(true);
		}

		return $this->render('TopxiaWebBundle:Announcement:announcement-write-modal.html.twig',array(
			'announcement' => array('id' => '', 'content' => ''),
			'targetObject' => $targetObject,
			'targetType' => $targetType,
		));
	}
	
	public function updateAction(Request $request, $id, $targetType, $targetId)
	{	
		$targetObject = $this->checkPermission($targetType, $targetId);
		
        $announcement = $this->getAnnouncementService()->getAnnouncement($targetId, $id);

	    if($request->getMethod() == 'POST') {
        	$this->getAnnouncementService()->updateAnnouncement($targetId, $id, $request->request->all());
	        return $this->createJsonResponse(true);
		}

		return $this->render('TopxiaWebBundle:Announcement:announcement-write-modal.html.twig',array(
			'targetObject' => $targetObject,
			'announcement' => $announcement,
			'targetType' => $targetType
		));
	}

	public function deleteAction(Request $request, $id, $targetType, $targetId)
	{
		$targetObject = $this->checkPermission($targetType, $targetId);
		
		$this->getAnnouncementService()->deleteAnnouncement($targetId, $id);

		return $this->createJsonResponse(true);
	}

	public function blockAction(Request $request, $targetObject, $targetType)
	{
		$conditions = array(
			'targetType' => $targetType,
			'targetId' => $targetObject['id']
		);

		list($canManage, $canTake) = $this->checkManageAndTake($targetObject, $targetType);

		$announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, array('createdTime','DESC'), 0, 10);

		return $this->render('TopxiaWebBundle:Announcement:announcement-block.html.twig',array(
			'targetObject' => $targetObject,
			'announcements' => $announcements,
			'canManage' => $canManage,
			'canTake' => $canTake,
			'targetType' => $targetType
		));
	}

	private function checkPermission($targetType, $targetId)
	{
		switch ($targetType) {
			case 'course':
				$targetObject = $this->getCourseService()->tryManageCourse($targetId);
				break;
			
			case 'classroom':
				$this->getClassroomService()->tryManageClassroom($targetId);
        		$targetObject = $this->getClassroomService()->getClassroom($targetId);
				break;
			
		}

		return $targetObject;
	}

	private function checkManageAndTake($targetObject, $targetType){
		switch ($targetType) {
			case 'course':
				$canManage = $this->getCourseService()->canManageCourse($targetObject['id']);
				$canTake = $this->getCourseService()->canTakeCourse($targetObject);
				break;
			
			case 'classroom':
				$canManage = $this->getClassroomService()->canManageClassroom($targetObject['id']);
				$canTake = $this->getClassroomService()->canTakeClassroom($targetObject['id']);
				break;
		}

		return array($canManage,$canTake);
	}

	private function announcementNotification($targetType, $targetId, $targetObject)
	{
		switch ($targetType) {
			case 'course':
				$count = $this->getCourseService()->getCourseStudentCount($targetId);
	        	$members = $this->getCourseService()->findCourseStudents($targetId, 0, $count);
	        	$url = $this->generateUrl('course_show', array('id'=>$targetId), true);
	        	$title = '课程';

				break;
			
			case 'classroom':
				$count = $this->getClassroomService()->searchMemberCount(array('classroomId'=>$targetId,'role'=>'student'));
	        	$members = $this->getClassroomService()->searchMembers(
		            array('classroomId'=>$targetId,'role'=>'student'),
		            array('createdTime','DESC'),
		            0,$count
		        );

	        	$url = $this->generateUrl('classroom_show', array('id'=>$targetId), true);
	        	$title = '班级';

				break;
			
		}

		$result = null;
		if ($members) {
			foreach ($members as $member) {
        		$result = $this->getNotificationService()->notify($member['userId'], 'default', "【{$title}公告】你正在学习的<a href='{$url}' target='_blank'>{$targetObject['title']}</a>发布了一个新的公告，<a href='{$url}' target='_blank'>快去看看吧</a>");
    		}
		}
		
		return $result;
	}

	protected function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getClassroomService()
    {
    	return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}