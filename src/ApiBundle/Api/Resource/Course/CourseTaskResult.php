<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseTaskResult extends AbstractResource
{
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('教学计划不存在');
        }

        return $this->service('Task:TaskResultService')->findUserTaskResultsByCourseId($courseId);
    }

}