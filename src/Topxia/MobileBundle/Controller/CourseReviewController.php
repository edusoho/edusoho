<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;

class CourseReviewController extends MobileController
{

    public function getAction(Request $request, $courseId, $reviewId)
    {
        $review = $this->getReviewService()->getReview($reviewId);
        if (empty($review)) {
            return $this->createErrorResponse($request, 'not_found', "评价#{$reviewId}找不到！");
        }

        $review = $this->filterReview($review);

        return $this->createJson($request, $review);
    }

    public function createAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录，不能评价课程！");
        }

        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return $this->createErrorResponse($request, 'not_found', "课程#{$courseId}不存在，不能评价！");
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createErrorResponse($request, 'access_denied', "您不是课程《{$course['title']}》学员，不能评价课程！");
        }

        $review = array();
        $review['courseId'] = $course['id'];
        $review['userId'] = $user['id'];
        $review['rating'] = $request->get('rating', 0);
        $review['content'] = $request->get('content','');

        $review = $this->getReviewService()->saveReview($review);
        $review = $this->filterReview($review);

        return $this->createJson($request, $review);
    }

    public function updateAction(Request $request, $courseId, $reviewId)
    {
        return $this->createAction($request, $courseId);
    }

    public function reviewsAction(Request $request, $courseId)
    {
        $result = array();
        $result['total'] = $this->getReviewService()->getCourseReviewCount($courseId);
        $result['start'] = (int) $request->query->get('start', 0);
        $result['limit'] = (int) $request->query->get('limit', 10);
        $reviews = $this->getReviewService()->findCourseReviews($courseId, 0, 100);
        $result['data'] = $this->filterReviews($reviews);
        return $this->createJson($request, $result);
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}