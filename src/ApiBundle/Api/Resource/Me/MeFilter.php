<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class MeFilter extends Filter
{
    public function filter(&$data)
    {
        $filter = new UserFilter();
        $filter->setMode(Filter::AUTHENTICATED_MODE);
        $filter->filter($data);
    }
}
