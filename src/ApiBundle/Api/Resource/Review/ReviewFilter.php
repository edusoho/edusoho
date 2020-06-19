<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ReviewFilter extends Filter
{
    protected $publicFields = [
        'id', 'userId', 'user', 'targetId', 'targetType', 'targetName', 'content', 'rating', 'parentId', 'createdTime', 'updatedTime',
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['user']);
        }
    }
}
