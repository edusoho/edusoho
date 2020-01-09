<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseThreadFilter extends Filter
{
    protected $publicFields = array(
        'id', 'courseId', 'taskId', 'type', 'isStick', 'isElite', 'isClosed', 'private', 'title', 'content', 'source', 'postNum', 'userId', 'attachments', 'attachments',
        'hitNum', 'followNum', 'questionType', 'latestPostUserId', 'videoAskTime', 'videoId', 'latestPostTime', 'courseSetId', 'createdTime', 'updatedTime', 'user', 'course', 'askVideoUri', 'askVideoLength', 'askVideoThumbnail', 'notReadPostNum', 'lastPost',
    );

    protected function publicFields(&$data)
    {
        if (isset($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->filter($data['user']);
        }

        if (isset($data['course'])) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::SIMPLE_MODE);
            $courseFilter->filter($data['course']);
        }

        if (isset($data['lastPost']) && !empty($data['lastPost'])) {
            $courseThreadPostFilter = new CourseThreadPostFilter();
            $courseThreadPostFilter->setMode(Filter::SIMPLE_MODE);
            $courseThreadPostFilter->filter($data['lastPost']);
        }

        if (isset($data['latestPostTime'])) {
            $data['latestPostTime'] = empty($data['latestPostTime']) ? 0 : date('c', $data['latestPostTime']);
        }
    }
}
