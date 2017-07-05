<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;

class ClassroomMemberFilter extends Filter
{
    protected $publicFields = array(
        'id', 'classroomId', 'userId', 'noteNum', 'threadNum', 'locked', 'role', 'deadline', 'access'
    );

    protected function publicFields(&$data)
    {
        if ($data['deadline']) {
            $data['deadline'] = date('c', $data['deadline']);
        }
    }
}