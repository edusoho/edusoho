<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;

class CourseSetCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new ResourceNotFoundException('课程不存在');
        }

        $courses = $this->service('Course:CourseService')->findPublishedCoursesByCourseSetId($courseSetId);
        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $courses;
    }
}