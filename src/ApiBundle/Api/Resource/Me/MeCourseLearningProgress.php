<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ApiNotFoundException;
use ApiBundle\Api\Resource\Resource;

class MeCourseLearningProgress extends Resource
{
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ApiNotFoundException('计划不存在');
        }

        return $this->service('Course:CourseService')->getUserLearningProcess($courseId, $this->getCurrentUser()->getId());
    }

}