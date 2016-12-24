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

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }
}
