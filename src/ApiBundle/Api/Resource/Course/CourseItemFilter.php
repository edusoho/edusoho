<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ServiceToolkit;

class CourseItemFilter extends Filter
{
    protected $publicFields = array(
        'id', 'type', 'number', 'seq', 'title', 'tasks', 'itemType'
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['tasks'])) {
            $tasKFilter = new CourseTaskFilter();
            $tasKFilter->filters($data['tasks']);
        }
    }
}