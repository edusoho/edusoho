<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\ClassroomException;

class JoinClassroomAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        if (empty($classroom)) {
            return $this->buildResult('NOTFOUND_CLASSROOM', array(), ClassroomException::EXCEPTION_MODUAL);
        }

        if ('draft' === $classroom['status']) {
            return $this->buildResult('UNPUBLISHED_CLASSROOM', array('classroomId' => $classroom['id']), ClassroomException::EXCEPTION_MODUAL);
        }

        if ('closed' === $classroom['status']) {
            return $this->buildResult('CLOSED_CLASSROOM', array('classroomId' => $classroom['id']), ClassroomException::EXCEPTION_MODUAL);
        }

        if (!$classroom['buyable']) {
            return $this->buildResult('UNBUYABLE_CLASSROOM', array('classroomId' => $classroom['id']), ClassroomException::EXCEPTION_MODUAL);
        }

        if ($this->isExpired($classroom)) {
            return $this->buildResult('EXPIRED_CLASSROOM', array('classroomId' => $classroom['id']), ClassroomException::EXCEPTION_MODUAL);
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
