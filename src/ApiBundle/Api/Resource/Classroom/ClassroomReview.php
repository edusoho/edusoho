<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Review\Service\ReviewService;

class ClassroomReview extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $classroomId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $conditions = $request->query->all();
        $conditions['classroomId'] = $classroomId;
        $conditions['parentId'] = 0;
        $conditions['targetId'] = $classroomId;
        $conditions['targetType'] = 'classroom';
        $conditions['offset'] = $offset;
        $conditions['limit'] = $limit;

        $total = $this->getReviewService()->countReviews($conditions);

        $reviews = $this->invokeResource(new ApiRequest(
            '/api/review',
            'GET',
            $conditions
        ));

        $this->getOCUtil()->multiple($reviews, ['userId']);

        foreach ($reviews as &$review) {
            $reviewPosts = $this->invokeResource(new ApiRequest(
                '/api/review',
                'GET',
                [
                    'parentId' => $review['id'],
                    'orderBys' => ['createdTime' => 'ASC'],
                    'offset' => 0,
                    'limit' => 5,
                ]
            ));

            $this->getOCUtil()->multiple($reviewPosts, ['userId']);
            $review['posts'] = $reviewPosts;
        }

        return $this->makePagingObject($reviews, $total, $offset, $limit);
    }

    /**
     * @return ReviewService
     */
    private function getReviewService()
    {
        return $this->service('Review:ReviewService');
    }
}
