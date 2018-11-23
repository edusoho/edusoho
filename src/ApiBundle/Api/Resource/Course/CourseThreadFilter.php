<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseThreadFilter extends Filter
{
    protected $publicFields = array(
        'id', 'courseId', 'taskId', 'type', 'isStick', 'isElite', 'isClosed', 'private', 'title', 'content', 'source', 'postNum', 'userId',
        'hitNum', 'followNum', 'latestPostUserId', 'videoAskTime', 'videoId', 'latestPostTime', 'courseSetId', 'createdTime', 'updatedTime', 'user', 'videoUri',
    );

    protected function publicFields(&$data)
    {
        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }
}
