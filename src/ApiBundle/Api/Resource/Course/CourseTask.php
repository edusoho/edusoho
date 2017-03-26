<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class CourseTask extends Resource
{
    public function search(Request $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在', 9);
        }

        return $this->service('Task:TaskService')->findTasksByCourseId($courseId);
    }

}