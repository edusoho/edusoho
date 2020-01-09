<?php

namespace ApiBundle\Api\Resource\Conversation;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;

class ConversationFilter extends Filter
{
    protected $publicFields = array(
        'convNo', 'user', 'classroom', 'course',
    );

    protected function publicFields(&$data)
    {
        if (isset($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['user']);
        }

        if (isset($data['classroom'])) {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::SIMPLE_MODE);
            $classroomFilter->filter($data['classroom']);
        }

        if (isset($data['course'])) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::SIMPLE_MODE);
            $courseFilter->filter($data['course']);
        }
    }
}
