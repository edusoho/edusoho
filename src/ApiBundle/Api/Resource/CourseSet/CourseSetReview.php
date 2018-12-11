<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseSetReview extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new NotFoundHttpException('课程不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $conditions = array(
            'courseSetId' => $courseSetId,
            'private' => 0,
            'parentId' => 0,
        );

        $offset = $request->query->get('offset', static::DEFAULT_PAGING_OFFSET);
        $limit = $request->query->get('limit', static::DEFAULT_PAGING_LIMIT);
        $total = $this->getCourseReviewService()->searchReviewsCount($conditions);
        $reviews = $this->getCourseReviewService()->searchReviews(
            $conditions,
            array('updatedTime' => 'DESC'),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($reviews, array('userId'));
        $this->getOCUtil()->multiple($reviews, array('courseId'), 'course');
        foreach ($reviews as &$review) {
            $review['posts'] = $this->getCourseReviewService()->searchReviews(array('parentId' => $review['id']), array('updatedTime' => 'DESC'), 0, 5);
            $this->getOCUtil()->multiple($review['posts'], array('userId'));
            $this->getOCUtil()->multiple($review['posts'], array('courseId'), 'course');
        }

        return $this->makePagingObject($reviews, $total, $offset, $limit);
    }

    private function getCourseReviewService()
    {
        return $this->service('Course:ReviewService');
    }
}
