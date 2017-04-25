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

        if ($classroom['status'] === 'draft') {
            return $this->buildResult('classroom.unpublished');
        }

        if ($this->isExpired($classroom)) {
            return $this->buildResult('classroom.expired');
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
