<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;

class ClassroomCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseFilter")
     */
    public function search(ApiRequest $request, $classroomId)
    {
        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);

        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');
        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));

        return $courses;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
