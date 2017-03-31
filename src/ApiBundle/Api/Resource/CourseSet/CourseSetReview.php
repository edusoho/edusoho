<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class CourseSetReview extends Resource
{
    public function search(Request $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new ResourceNotFoundException('课程不存在');
        }

        $conditions = array(
            'courseSetId' => $courseSetId,
            'courseId' => $request->query->get('courseId'),
            'private' => 0
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