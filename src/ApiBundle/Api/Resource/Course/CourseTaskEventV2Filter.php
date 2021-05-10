<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskEventV2Filter extends Filter
{
    protected $publicFields = [
        'taskResult', 'event', 'nextTask', 'completionRate', 'record', 'watchResult', 'learnControl', 'learnedTime',
    ];

    protected function publicFields(&$data)
    {
        $taskResultFilter = new CourseTaskResultFilter();
        $taskResultFilter->filter($data['taskResult']);

        $taskFilter = new CourseTaskFilter();
        $taskFilter->filter($data['nextTask']);
    }
}
