<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class CourseSet extends Resource
{
    public function get(Request $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            throw new ResourceNotFoundException('课程不存在');
        }

        $this->getOCUtil()->single($courseSet, array('creator'));

        return $courseSet;
    }

    public function add()
    {

    }

}