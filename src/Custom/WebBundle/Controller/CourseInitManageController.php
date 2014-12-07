<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;

class CourseInitManageController extends BaseController
{
	public function indexAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

		$paginator = new Paginator(
		    $this->get('request'),
		    $this->getReviewService()->getCourseReviewCount($id)
		    , 10
		);
		$condition = array(
			'courseId' => $course['id'],
			'isInit' => 1,
		);
		$reviews = $this->getReviewService()->searchReviews(
		    $condition,
		    'latest',
		    $paginator->getOffsetCount(),
		    $paginator->getPerPageCount()
		);

		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

		return $this->render('CustomWebBundle:CourseInitManage:index.html.twig', array(
		    'course' => $course,
		    'reviews' => $reviews,
		    'users' => $users,
		    'paginator' => $paginator
		));
	}

	public function createAction(Request $request, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($id);

		if ($request->getMethod() == 'POST') {
			$fields = $request->request->all();
	        $fields['rating'] = $fields['rating'];
	        $fields['courseId']= $id;
	        $this->getReviewService()->saveReview($fields);
	        return $this->createJsonResponse(true);
		}

		return $this->render('CustomWebBundle:CourseInitManage:write-modal.html.twig', array(
		    'course' => $course,
		));
	}

	private function getCourseService()
	{
	    return $this->getServiceKernel()->createService('Course.CourseService');
	}

	private function getReviewService()
	{
	    return $this->getServiceKernel()->createService('Course.ReviewService');
	}
}