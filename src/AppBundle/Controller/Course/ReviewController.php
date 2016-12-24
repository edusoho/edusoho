<?php

namespace AppBundle\Controller\Course;

use Biz\BaseController;

class ReviewController extends BaseController
{
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }
}
