<?php

namespace ApiBundle\Api\Resource\App;

use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;

class AppChannelFilter extends Filter
{
    protected $publicFields = array(
        'title', 'type', 'data', 'showCount', 'actualCount', 'orderType', 'categoryId',
    );

    protected function publicFields(&$data)
    {
        if ($data['type'] == 'course' || $data['type'] == 'live') {
            $courseSetFilter = new CourseSetFilter();
            $courseSetFilter->setMode(Filter::SIMPLE_MODE);
            $courseSetFilter->filters($data['data']);
        }

        if ($data['type'] == 'classroom') {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::SIMPLE_MODE);
            $classroomFilter->filters($data['data']);
        }
    }
}
