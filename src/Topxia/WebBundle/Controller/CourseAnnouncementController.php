<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;


class CourseAnnouncementController extends BaseController
{

	public function showAction(Request $request, $courseId, $id)
	{
		$course = $this->getCourseService()->tryTakeCourse($courseId);
        $announcement = $this->getCourseService()->getCourseAnnouncement($courseId, $id);
		return $this->render('TopxiaWebBundle:Course:announcement-show-modal.html.twig',array(
			'announcement' => $announcement,
			'course' => $course,
		));
	}

	public function showAllAction(Request $request, $courseId)
	{

		$announcements = $this->getCourseService()->findAnnouncements($courseId, 0, 10000);
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($announcements, 'userId'));
		return $this->render('TopxiaWebBundle:Course:announcement-show-all-modal.html.twig',array(
			'announcements'=>$announcements,
			'users'=>$users
		));
	}

	public function createAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

	    if($request->getMethod() == 'POST'){
        	$announcement = $this->getCourseService()->createAnnouncement($courseId, $request->request->all());

        	return $this->createJsonResponse(true);
		}

		return $this->render('TopxiaWebBundle:Course:announcement-write-modal.html.twig',array(
			'announcement' => array('id' => '', 'content' => ''),
			'course'=>$course,
		));
	}
	
	public function updateAction(Request $request, $courseId, $id)
	{	
		$course = $this->getCourseService()->tryManageCourse($courseId);

        $announcement = $this->getCourseService()->getCourseAnnouncement($courseId, $id);
        if (empty($announcement)) {
        	return $this->createNotFoundException("课程公告(#{$id})不存在。");
        }

	    if($request->getMethod() == 'POST') {
        	$this->getCourseService()->updateAnnouncement($courseId, $id, $request->request->all());
	        return $this->createJsonResponse(true);
		}

		return $this->render('TopxiaWebBundle:Course:announcement-write-modal.html.twig',array(
			'course' => $course,
			'announcement'=>$announcement,
		));
	}

	public function deleteAction(Request $request, $courseId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$this->getCourseService()->deleteCourseAnnouncement($courseId, $id);
		return $this->createJsonResponse(true);
	}

	public function blockAction(Request $request, $course)
	{
		$announcements = $this->getCourseService()->findAnnouncements($course['id'], 0, 10);
		return $this->render('TopxiaWebBundle:Course:announcement-block.html.twig',array(
			'course' => $course,
			'announcements' => $announcements,
			'canManage' => $this->getCourseService()->canManageCourse($course),
			'canTake' => $this->getCourseService()->canTakeCourse($course)
		));
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

}