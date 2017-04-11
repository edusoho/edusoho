<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use ApiBundle\Api\Annotation\ApiConf;

class CourseSet extends Resource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            throw new ResourceNotFoundException('课程不存在');
        }

        $this->getOCUtil()->single($courseSet, array('creator', 'teacherIds'));

        return $courseSet;
    }

    public function add()
    {

    }

}