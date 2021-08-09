<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseNoteFilter extends Filter
{
    protected $publicFields = ['id', 'userId', 'user', 'taskId', 'content', 'length', 'likeNum', 'createdTime', 'updatedTime'];

    public function publicFields(&$data)
    {
        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['user']);
        }
    }
}
