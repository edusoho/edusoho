<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class CourseTaskMaterial extends AbstractResource
{
    /**
     * TODO 需要权限
     *
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $taskId, $materialId)
    {
        return array($courseId, $taskId, $materialId);
    }
}
