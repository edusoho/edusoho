<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'isFree', 'startTime', 'endTime', 'status', 'length', 'mode', 'type', 'result', 'lock'
    );

    public function simpleFields(&$data)
    {
        if (!empty($data['result'])) {
            $taskFilter = new CourseTaskFilter();
            $taskFilter->setMode(Filter::SIMPLE_MODE);
            $taskFilter->filter($data['result']);
        }

    }
}