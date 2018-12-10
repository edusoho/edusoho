<?php

namespace Biz\Classroom\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Classroom\ClassroomException;

class LearnClassroomAccessor extends AccessorAdapter
{
    public function access($classroom)
    {
        if (empty($classroom)) {
            return $this->buildResult('NOTFOUND_CLASSROOM', array(), ClassroomException::EXCEPTION_MODUAL);
        }

        if ($classroom['status'] === 'draft') {
            return $this->buildResult('UNPUBLISHED_CLASSROOM', array('classroomId' => $classroom['id']), ClassroomException::EXCEPTION_MODUAL);
        }

        if ($this->isExpired($classroom)) {
            return $this->buildResult('EXPIRED_CLASSROOM', array('classroomId' => $classroom['id']), ClassroomException::EXCEPTION_MODUAL);
        }

        return null;
    }

    public function isExpired($classroom)
    {
        $expiryMode = $classroom['expiryMode'];

        if ($expiryMode === 'date') {
            return time() > $classroom['expiryValue'];
        }

        return false;
    }
}
