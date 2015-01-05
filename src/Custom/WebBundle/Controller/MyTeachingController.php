<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class MyTeachingController extends BaseController
{
	public function dashboardAction(Request $request)
	{
		$user = $this->getCurrentUser();

		if(!$user->isTeacher()) {
		    return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
		}

		return $this->render('CustomWebBundle:MyTeaching:dashboard.html.twig', array(
		));
	}

	public function myCoursesRatingAction(Request $request)
	{
		$user = $this->getCurrentUser();
		$teachCoursesCount = $this->getCourseService()->findUserTeachCourseCount($user['id']);
		$teachCourses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, $teachCoursesCount);
		$teachCourses = ArrayToolkit::index($teachCourses, 'id');
		return $this->render('CustomWebBundle:MyTeaching:my-courses-rating.html.twig', array(
			'teachCourses' => $teachCourses,
		));
	}

	protected function getCourseService()
	{
	    return $this->getServiceKernel()->createService('Course.CourseService');
	}

}