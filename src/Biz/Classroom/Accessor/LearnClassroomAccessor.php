<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;

class LearnClassroomAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        if (empty($classroom)) {
            return $this->buildResult('classroom.not_found');
        }

        if ('draft' === $classroom['status']) {
            return $this->buildResult('classroom.unpublished', array('classroomId' => $classroom['id']));
        }

        return null;
    }
}
