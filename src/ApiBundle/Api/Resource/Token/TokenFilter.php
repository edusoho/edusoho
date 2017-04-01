<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class TokenFilter extends Filter
{
    protected function customFilter(&$data)
    {
        $userFilter = new UserFilter();
        $userFilter->setFieldMode('token');
        $userFilter->filter($data['user']);
    }
}