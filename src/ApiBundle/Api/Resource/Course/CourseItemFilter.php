<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ServiceToolkit;

class CourseItemFilter extends Filter
{
    protected $publicFields = array(
        'type', 'number', 'seq', 'title', 'task', 'itemType'
    );

    protected function publicFields(&$data)
    {
        $tasKFilter = new CourseTaskFilter();
        $tasKFilter->filter($data['task']);
    }
}