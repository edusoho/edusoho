<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskResultFilter extends Filter
{
    protected $simpleFields = array(
        'status'
    );
}