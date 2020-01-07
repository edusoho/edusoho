<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ClassroomThreadPostFilter extends Filter
{
    protected $publicFields = array(
        'id',
        'threadId',
        'content',
        'adopted',
        'ats',
        'userId',
        'parentId',
        'subposts',
        'ups',
        'targetType',
        'targetId',
        'createdTime',
        'user',
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['user']);
        }
    }
}
