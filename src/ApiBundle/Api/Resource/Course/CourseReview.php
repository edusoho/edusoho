<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\CourseException;

/**
 * Class CourseReview
 *
 * @OA\Info(title="课程评价", version="0.1")
 */
class CourseReview extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     *
     * @OA\Get(
     *     path="/course/{courseId}/reviews",
     *     @OA\Response(response="200", description="获取课程的评价")
     * )
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        return $this->invokeResource(new ApiRequest(
            '/api/review',
            'GET',
            [
                'targetType' => 'course',
                'targetId' => $courseId,
                'offset' => $request->query->get('offset', static::DEFAULT_PAGING_OFFSET),
                'limit' => $request->query->get('limit', static::DEFAULT_PAGING_LIMIT),
                'orderBys' => ['updatedTime' => 'DESC'],
            ]
        ));
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
}
