<?php

namespace AppBundle\Controller\Course;

use Biz\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends BaseController
{
    public function listAction(Request $request, $courseSetId)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
            'parentId'    => 0
        );

        $courseId = $request->query->get('courseId');

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            10
        );
    }

    public function postAction(Request $request, $courseId, $reviewId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $postNum = $this->getReviewService()->searchReviewsCount(array('parentId' => $reviewId));

        if ($postNum >= 5) {
            return $this->createJsonResponse(array('error' => $this->trans('回复数量已达5条上限，不能再回复')));
        }

        $user = $this->getCurrentUser();

        $fields             = $request->request->all();
        $fields['userId']   = $user['id'];
        $fields['courseId'] = $course['id'];
        $fields['rating']   = 1;
        $fields['parentId'] = $reviewId;

        $post = $this->getReviewService()->saveReview($fields);

        return $this->render('review/widget/subpost-item.html.twig', array(
            'post'      => $post,
            'author'    => $this->getCurrentUser(),
            'canAccess' => true
        ));
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }
}
