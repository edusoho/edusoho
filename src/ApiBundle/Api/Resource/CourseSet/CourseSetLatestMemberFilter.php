<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;

class CourseSetLatestMemberFilter extends Filter
{
    public function filter(&$data)
    {
        $courseMember = new CourseMemberFilter();
        $courseMember->filter($data);
    }
}
