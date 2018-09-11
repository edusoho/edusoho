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
        $tasKFilter = new CourseItemFilter();
        if (!empty($data['tasks'])) {
            foreach ($data['tasks'] as &$task) {
                $tasKFilter->filter($task);
            }
        } else {
            $tasKFilter->filter($data);
        }
    }
}
