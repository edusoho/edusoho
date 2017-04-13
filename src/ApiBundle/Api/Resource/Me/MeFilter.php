<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class MeFilter extends Filter
{
    protected function customFilter(&$data)
    {
        $filter = new UserFilter();
        $filter->setFieldMode('token');
        $filter->filter($data);
    }
}