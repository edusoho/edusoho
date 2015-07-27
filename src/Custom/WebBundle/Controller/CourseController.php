<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\CourseController as BaseCourseController;


class CourseController extends BaseCourseController
{
    public function showAction(Request $request, $id)
    {
	list ($course, $member) = $this->buildCourseLayoutData($request, $id);
	if(empty($member)) {
		$user = $this->getCurrentUser();
		$member = $this->getCourseService()->becomeStudentByClassroomJoined($id, $user->id);
		if(isset($member["id"])) {
			$course['studentNum'] ++ ;
		}
	}

	$this->getCourseService()->hitCourse($id);
        $items = $this->getCourseService()->getCourseItems($course['id']);

	return $this->render(($course['type']=='periodic') ? "CustomWebBundle:Course:{$course['type']}-show.html.twig" : "TopxiaWebBundle:Course:{$course['type']}-show.html.twig", array(
		'course' => $course,
		'member' => $member,
		'items' => $items,
	));
    }
}