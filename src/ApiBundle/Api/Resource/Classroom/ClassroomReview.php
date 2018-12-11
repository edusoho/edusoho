<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ApiConf;

class ClassroomReview extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $classroomId)
    {
        $conditions = $request->query->all();
        $conditions['classroomId'] = $classroomId;
        $conditions['parentId'] = 0;
        $total = $this->getClassroomReviewService()->searchReviewCount($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $reviews = $this->getClassroomReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );
        $this->getOCUtil()->multiple($reviews, array('userId'));
        foreach ($reviews as &$review) {
            $reviewPosts = $this->getClassroomReviewService()->searchReviews(array('parentId' => $review['id']), array('createdTime' => 'ASC'), 0, 5);
            $this->getOCUtil()->multiple($reviewPosts, array('userId'));
            $review['posts'] = $reviewPosts;
        }

        return $this->makePagingObject($reviews, $total, $offset, $limit);
    }

    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    private function getClassroomReviewService()
    {
        return $this->service('Classroom:ClassroomReviewService');
    }
}
