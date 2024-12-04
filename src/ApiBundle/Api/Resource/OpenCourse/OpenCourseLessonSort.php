<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourseLessonSort extends AbstractResource
{
    public function add(ApiRequest $request, $courseId)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($courseId);
    }

    /**
     * @return OpenCourseService
     */
    private function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }
}
