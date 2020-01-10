<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class TokenFilter extends Filter
{
    protected $publicFields = array(
        'token', 'user', 'success',
    );

    protected function publicFields(&$data)
    {
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::AUTHENTICATED_MODE);
        $userFilter->filter($data['user']);
    }
}
