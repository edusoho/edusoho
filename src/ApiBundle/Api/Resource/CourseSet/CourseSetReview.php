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
        );

        $offset = $request->query->get('offset', static::DEFAULT_PAGING_OFFSET);
        $limit = $request->query->get('limit', static::DEFAULT_PAGING_LIMIT);
        $reviews = $this->service('Course:ReviewService')->searchReviews(
            $conditions,
            array('updatedTime' => 'DESC'),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($reviews, array('userId'));
        $this->getOCUtil()->multiple($reviews, array('courseId'), 'course');

        $total = $this->service('Course:ReviewService')->searchReviewsCount($conditions);

        return $this->makePagingObject($reviews, $total, $offset, $limit);
    }
}
