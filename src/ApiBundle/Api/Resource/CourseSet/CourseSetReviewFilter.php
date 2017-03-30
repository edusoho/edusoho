<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseSetReviewFilter extends Filter
{
    protected function customFilter(&$data)
    {
        //评价的用户
        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }
}