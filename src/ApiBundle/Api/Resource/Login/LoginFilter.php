<?php

namespace ApiBundle\Api\Resource\Login;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class LoginFilter extends Filter
{
    protected $publicFields = array(
        'token', 'user',
    );

    protected function publicFields(&$data)
    {
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::AUTHENTICATED_MODE);
        $userFilter->filter($data['user']);
    }
}
