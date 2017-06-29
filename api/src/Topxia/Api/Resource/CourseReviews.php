<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CourseReviews extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $fields = $request->query->all();

        $conditions = array(
            'courseId' => $courseId
        );
        
        $total = $this->getCourseReviewService()->searchReviewsCount($conditions);
        $reviews = $this->getCourseReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            isset($fields['start']) ? (int) $fields['start'] : 0,
            isset($fields['limit']) ? (int) $fields['limit'] : 20
        );
        return $this->wrap($this->multicallFilter('CourseReviews', $reviews), $total);
    }

    public function post(Application $app, Request $request, $courseId)
    {
        $requiredFields = array('rating', 'content');
        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

        $review = array(
            'courseId' => $courseId,
            'userId' => !empty($fields['userId']) ? $fields['userId'] : $this->getCurrentUser()->id,
            'rating' => $fields['rating'],
            'content' => $fields['content'],
        );
        $review = $this->getCourseReviewService()->saveReview($review);
        return $this->filter($review);
    }

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        return $res;
    }

    protected function getCourseReviewService()
    {
        return $this->getServiceKernel()->createService('Course:ReviewService');
    }
}
