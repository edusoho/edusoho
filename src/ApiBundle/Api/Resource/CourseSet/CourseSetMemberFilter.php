<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseSetMemberFilter extends Filter
{
    public function filter(&$data)
    {
        $courseMember = new CourseMemberFilter();
        $courseMember->filter($data);
    }
}