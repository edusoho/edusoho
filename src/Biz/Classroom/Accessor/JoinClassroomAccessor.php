<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;

class JoinClassroomAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        if (empty($classroom)) {
            return $this->buildResult('classroom.not_found');
        }

        if ('draft' === $classroom['status']) {
            return $this->buildResult('classroom.unpublished', array('classroomId' => $classroom['id']));
        }

        if ('closed' === $classroom['status']) {
            return $this->buildResult('classroom.closed', array('classroomId' => $classroom['id']));
        }

        if (!$classroom['buyable']) {
            return $this->buildResult('classroom.not_buyable', array('classroomId' => $classroom['id']));
        }

        if ($this->isExpired($classroom)) {
            return $this->buildResult('classroom.expired', array('classroomId' => $classroom['id']));
        }

        return null;
    }

    public function isExpired($classroom)
    {
        $expiryMode = $classroom['expiryMode'];

        if ('date' === $expiryMode) {
            return time() > $classroom['expiryValue'];
        }

        return false;
    }
}
