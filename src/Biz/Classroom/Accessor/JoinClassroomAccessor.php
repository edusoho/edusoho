<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\ClassroomException;

class JoinClassroomAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        if (empty($classroom)) {
            return $this->buildResult(ClassroomException::EXCEPTION_MODUAL, 'NOTFOUND_CLASSROOM');
        }

        if ('draft' === $classroom['status']) {
            return $this->buildResult(ClassroomException::EXCEPTION_MODUAL, 'UNPUBLISHED_CLASSROOM', array('classroomId' => $classroom['id']));
        }

        if ('closed' === $classroom['status']) {
            return $this->buildResult(ClassroomException::EXCEPTION_MODUAL, 'CLOSED_CLASSROOM', array('classroomId' => $classroom['id']));
        }

        if (!$classroom['buyable']) {
            return $this->buildResult(ClassroomException::EXCEPTION_MODUAL, 'UNBUYABLE_CLASSROOM', array('classroomId' => $classroom['id']));
        }

        if ($this->isExpired($classroom)) {
            return $this->buildResult(ClassroomException::EXCEPTION_MODUAL, 'EXPIRED_CLASSROOM', array('classroomId' => $classroom['id']));
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
