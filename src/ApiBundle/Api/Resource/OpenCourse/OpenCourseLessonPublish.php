<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourseLessonPublish extends AbstractResource
{
    public function add(ApiRequest $request, $courseId, $lessonId)
    {
        $this->getOpenCourseService()->publishLesson($courseId, $lessonId);

        return ['ok' => true];
    }

    public function remove(ApiRequest $request, $courseId, $lessonId)
    {
        $this->getOpenCourseService()->unpublishLesson($courseId, $lessonId);

        return ['ok' => true];
    }

    /**
     * @return OpenCourseService
     */
    private function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }
}
