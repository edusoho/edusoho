<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseItemFilter extends Filter
{
    protected $publicFields = array(
        'type', 'number', 'seq', 'title', 'task', 'itemType', 'status', 'isExist', 'children',
    );

    protected function publicFields(&$data)
    {
        $tasKFilter = new CourseTaskFilter();
        $tasKFilter->filter($data['task']);
    }
}
