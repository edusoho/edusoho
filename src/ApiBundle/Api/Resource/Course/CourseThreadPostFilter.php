<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseThreadPostFilter extends Filter
{
    protected $publicFields = array(
        'id', 'courseId', 'taskId', 'threadId', 'isElite', 'content', 'source', 'isRead', 'userId', 'createdTime', 'user', 'attachments',
    );

    protected function publicFields(&$data)
    {
        if (isset($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->filter($data['user']);
        }
    }
}
