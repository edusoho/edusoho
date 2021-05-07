<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ReviewPostFilter extends Filter
{
    protected $simpleFields = [
        'id', 'userId', 'user', 'targetId', 'content', 'rating', 'me_report', 'parentId', 'updatedTime', 'createdTime',
    ];

    protected $publicFields = [
        'id', 'userId', 'user', 'targetId', 'targetType', 'content', 'rating', 'me_report', 'parentId', 'createdTime', 'updatedTime', 'template',
    ];

    protected function simpleFields(&$data)
    {
        $this->filterUser($data);
    }

    protected function publicFields(&$data)
    {
        $this->filterUser($data);
    }

    protected function filterUser(&$data)
    {
        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['user']);
        }
    }
}
