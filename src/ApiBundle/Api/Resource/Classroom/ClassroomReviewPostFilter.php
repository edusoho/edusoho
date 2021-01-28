<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ClassroomReviewPostFilter extends Filter
{
    protected $publicFields = array(
        'id', 'user', 'classroomId', 'content', 'rating', 'parentId', 'updatedTime', 'createdTime',
    );

    protected function publicFields(&$data)
    {
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['user']);
    }
}
