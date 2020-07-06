<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Review\Service\ReviewService;

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

        $conditions = [
            'targetType' => 'course',
            'targetId' => $courseId,
        ];

        $offset = $request->query->get('offset', static::DEFAULT_PAGING_OFFSET);
        $limit = $request->query->get('limit', static::DEFAULT_PAGING_LIMIT);

        $total = $this->getReviewService()->countReviews($conditions);

        $reviews = $this->searchReviews($conditions, $offset, $limit);

        return $this->makePagingObject($reviews, $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $courseId)
    {
        $rating = $request->request->get('rating');
        $content = $request->request->get('content');

        if (empty($rating) || empty($content)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->invokeResource(new ApiRequest(
            '/api/reviews',
            'POST',
            [],
            [
                'targetType' => 'course',
                'targetId' => $courseId,
                'userId' => $this->getCurrentUser()->getId(),
                'rating' => $request->request->get('rating'),
                'content' => $request->request->get('content'),
            ]
        ));
    }

    protected function searchReviews($conditions, $offset, $limit)
    {
        $reviews = $this->invokeResource(new ApiRequest(
            '/api/reviews',
            'GET',
            array_merge($conditions, [
                'offset' => $offset,
                'limit' => $limit,
                'orderBys' => ['updatedTime' => 'DESC'],
            ])
        ));

        $this->getOCUtil()->multiple($reviews, ['userId']);
        $this->getOCUtil()->multiple($reviews, ['targetId'], 'course');

        array_filter($reviews, function (&$review) {
            $review['course'] = $review['target'];
            unset($review['target']);
        });

        return $reviews;
    }

    /**
     * @return ReviewService
     */
    private function getReviewService()
    {
        return $this->service('Review:ReviewService');
    }
}
