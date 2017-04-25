<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskEventFilter extends Filter
{
    protected $publicFields = array(
        'result', 'event'
    );

    protected function publicFields(&$data)
    {
        $courseTaskFilter = new CourseTaskResultFilter();
        $courseTaskFilter->filter($data['result']);
    }
}