<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskEventFilter extends Filter
{
    protected $publicFields = array(
        'result', 'event', 'nextTask', 'completionRate', 'lastTime',
    );

    protected function publicFields(&$data)
    {
        $taskResultFilter = new CourseTaskResultFilter();
        $taskResultFilter->filter($data['result']);

        $taskFilter = new CourseTaskFilter();
        $taskFilter->filter($data['nextTask']);
    }
}
