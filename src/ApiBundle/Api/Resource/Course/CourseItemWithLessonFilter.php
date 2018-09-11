<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseItemWithLessonFilter extends Filter
{
    protected $publicFields = array(
        'type', 'number', 'seq', 'title', 'isOptional', 'tasks',
    );

    protected function publicFields(&$data)
    {
        var_dump($data);
        $tasKFilter = new CourseItemFilter();
        foreach ($data['tasks'] as &$task) {
            $tasKFilter->filter($task);
        }
    }
}
