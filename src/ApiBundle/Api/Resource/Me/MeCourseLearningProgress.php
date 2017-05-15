<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MeCourseLearningProgress extends AbstractResource
{
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        return $this->getCourseService()->getUserLearningProcess($courseId, $this->getCurrentUser()->getId());
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}