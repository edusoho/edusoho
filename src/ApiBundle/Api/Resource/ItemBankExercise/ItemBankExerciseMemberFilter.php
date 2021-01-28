<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ItemBankExerciseMemberFilter extends Filter
{
    protected $publicFields = [
        'id', 'exerciseId', 'questionBankId', 'role', 'locked', 'user',
    ];

    protected function publicFields(&$data)
    {
        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }
}
