<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;

class CourseLearningProgress extends Resource
{
    public function search(ApiRequest $request, $courseId)
    {
        $this->service('Course:CourseService')->
        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    public function add()
    {

    }

}