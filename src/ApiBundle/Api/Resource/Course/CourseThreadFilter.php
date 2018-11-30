<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseThreadFilter extends Filter
{
    protected $publicFields = array(
        'id', 'courseId', 'taskId', 'type', 'isStick', 'isElite', 'isClosed', 'private', 'title', 'content', 'source', 'postNum', 'userId', 'attachments', 'attachments',
        'hitNum', 'followNum', 'latestPostUserId', 'videoAskTime', 'videoId', 'latestPostTime', 'courseSetId', 'createdTime', 'updatedTime', 'user', 'askVideoUri', 'askVideoLength', 'askVideoThumbnail',
    );

    protected function publicFields(&$data)
    {
        if (isset($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->filter($data['user']);
        }

        if (isset($data['latestPostTime'])) {
            $data['latestPostTime'] = empty($data['latestPostTime']) ? 0 : date('c', $data['latestPostTime']);
        }
    }
}
