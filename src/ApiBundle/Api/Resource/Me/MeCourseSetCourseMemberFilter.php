<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;

class MeCourseSetCourseMemberFilter extends Filter
{
    public function filter(&$data)
    {
        $filter = new CourseMemberFilter();
        $filter->filter($data);
    }
}
