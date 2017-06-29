<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends BaseController
{
    public function createAction(Request $request, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

        $fields = $request->request->all();
        $fields['courseId'] = $id;
        $fields['userId'] = $this->getCurrentUser()->getId();
        $this->getReviewService()->saveReview($fields);

        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $courseId, $reviewId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $postNum = $this->getReviewService()->searchReviewsCount(array('parentId' => $reviewId));

        if ($postNum >= 5) {
            return $this->createJsonResponse(array('error' => '回复数量已达5条上限，不能再回复'));
        }

        $user = $this->getCurrentUser();

        $fields = $request->request->all();
        $fields['userId'] = $user['id'];
        $fields['courseId'] = $course['id'];
        $fields['rating'] = 1;
        $fields['parentId'] = $reviewId;

        $post = $this->getReviewService()->saveReview($fields);

        return $this->render('review/widget/subpost-item.html.twig', array(
            'post' => $post,
            'author' => $this->getCurrentUser(),
            'canAccess' => true,
        ));
    }

    public function deleteAction(Request $request, $courseId, $reviewId)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $this->getReviewService()->deleteReview($reviewId);

        return $this->createJsonResponse(true);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }
}
