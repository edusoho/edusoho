<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;

class CourseItem extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $apiRequest, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        return array_values($this->getCourseService()->findCourseItems($courseId));
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}