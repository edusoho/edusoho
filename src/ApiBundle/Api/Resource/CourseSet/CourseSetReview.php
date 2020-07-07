<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseSetException;
use Biz\Course\Service\CourseService;
use Biz\Review\Service\ReviewService;

class CourseSetReview extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw CourseSetException::NOTFOUND_COURSESET();
        }

        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        $conditions = [
            'targetIds' => array_values(array_column($courses, 'id')),
            'parentId' => 0,
            'targetType' => 'course',
        ];

        $offset = $request->query->get('offset', static::DEFAULT_PAGING_OFFSET);
        $limit = $request->query->get('limit', static::DEFAULT_PAGING_LIMIT);
        $total = $this->getReviewService()->countReviews($conditions);
        $reviews = $this->searchReviews($conditions, $offset, $limit);

        return $this->makePagingObject($reviews, $total, $offset, $limit);
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
        foreach ($reviews as &$review) {
            $review['posts'] = $this->invokeResource(new ApiRequest(
                '/api/reviews',
                'GET',
                [
                    'parentId' => $review['id'],
                    'offset' => 0,
                    'limit' => 5,
                    'orderBys' => ['updatedTime' => 'DESC'],
                ]
            ));

            $this->getOCUtil()->multiple($review['posts'], ['userId']);
            $this->getOCUtil()->multiple($review['posts'], ['targetId'], 'course');

            array_filter($review['posts'], function (&$post) {
                $post['course'] = $post['target'];
                unset($post['target']);
            });

            $review['course'] = $review['target'];
            unset($review['target']);
        }

        return $reviews;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ReviewService
     */
    private function getReviewService()
    {
        return $this->service('Review:ReviewService');
    }
}
