<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;

class CourseSetCourse extends Resource
{
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new ResourceNotFoundException('课程不存在');
        }

        $courses = $this->service('Course:CourseService')->findPublishedCoursesByCourseSetId($courseSetId);
        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));

        return $courses;
    }
}