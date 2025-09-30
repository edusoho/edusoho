<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourseLessonSort extends AbstractResource
{
    public function add(ApiRequest $request, $courseId)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $ids = $request->request->get('ids');
        if (empty($ids)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $itemIds = [];
        foreach ($ids as $id) {
            $itemIds[] = "lesson-{$id}";
        }
        $this->getOpenCourseService()->sortCourseItems($courseId, $itemIds);

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
