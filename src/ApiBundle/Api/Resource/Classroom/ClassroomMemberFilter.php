<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ClassroomMemberFilter extends Filter
{
    protected $publicFields = array(
        'id', 'classroomId', 'userId', 'noteNum', 'threadNum', 'locked', 'role', 'deadline', 'access', 'user',
    );

    protected function publicFields(&$data)
    {
        if ($data['deadline']) {
            $data['deadline'] = date('c', $data['deadline']);
        }
        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }
}
