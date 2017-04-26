<?php

namespace ApiBundle\Api\Resource\Activity;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;

class ActivityFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'ext'
    );
}