<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'isFree', 'startTime', 'endTime', 'status', 'length', 'mode', 'type'
    );
}