<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ReviewPostFilter extends Filter
{
    protected $publicFields = [
        'id', 'userId', 'user', 'targetId', 'targetType', 'content', 'rating', 'parentId', 'createdTime', 'updatedTime', 'template',
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
