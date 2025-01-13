<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;

class ClassroomCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseFilter")
     */
    public function search(ApiRequest $request, $classroomId)
    {
        $title = trim($request->query->get('title', ''));
        if ($title) {
            $courses = $this->getClassroomService()->findSortedCoursesByClassroomIdAndCourseSetTitle($classroomId, $title);
        } else {
            $courses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        }

        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');
        $this->getOCUtil()->multiple($courses, ['creator', 'teacherIds']);
        foreach ($courses as &$course) {
            $course['videoMaxLevel'] = $this->getCourseService()->getVideoMaxLevel($course['id']);
        }

        return $courses;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
