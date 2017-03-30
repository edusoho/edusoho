<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class CourseSetCourse extends Resource
{
    public function search(Request $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new ResourceNotFoundException('课程不存在');
        }

        $courses = $this->service('Course:CourseService')->findPublishedCoursesByCourseSetId($courseSetId);
        $this->getUAUtil()->multiple($courses, array('creator', 'teacherIds'));

        return $courses;
    }
}