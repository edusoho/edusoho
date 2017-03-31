<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class Course extends Resource
{
    public function get(Request $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        $this->getOCUtil()->single($course, array('creator', 'teacherIds'));

        return $course;
    }

    public function add()
    {

    }

}