<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Common\CommonException;

class CourseReview extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $conditions = array(
            'courseId' => $courseId,
            'private' => 0,
        );

        $offset = $request->query->get('offset', static::DEFAULT_PAGING_OFFSET);
        $limit = $request->query->get('limit', static::DEFAULT_PAGING_LIMIT);
        $reviews = $this->getCourseReviewService()->searchReviews(
            $conditions,
            array('updatedTime' => 'DESC'),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($reviews, array('userId'));
        $this->getOCUtil()->multiple($reviews, array('courseId'), 'course');

        $total = $this->getCourseReviewService()->searchReviewsCount($conditions);

        return $this->makePagingObject($reviews, $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $courseId)
    {
        $rating = $request->request->get('rating');
        $content = $request->request->get('content');

        if (empty($rating) || empty($content)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $review = array(
            'courseId' => $courseId,
            'userId' => $this->getCurrentUser()->id,
            'rating' => $rating,
            'content' => $content,
        );

        return $this->getCourseReviewService()->saveReview($review);
    }

    private function getCourseReviewService()
    {
        return $this->service('Course:ReviewService');
    }
}
