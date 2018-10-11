<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;

class CourseTaskResult extends AbstractResource
{
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        return $this->service('Task:TaskResultService')->findUserTaskResultsByCourseId($courseId);
    }
}
