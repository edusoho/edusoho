<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;

class MeJoinInCourseFilter extends Filter
{

    protected function customFilter(&$data)
    {
        $filter = new CourseMemberFilter();
        $filter->filter($data);
    }
}