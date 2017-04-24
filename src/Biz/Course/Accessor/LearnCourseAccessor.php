<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;

class LearnCourseAccessor extends AccessorAdapter
{
    public function access($course)
    {
        if ($course['status'] === 'draft') {
            return $this->buildResult('course.unpublished');
        }

        if ($this->isExpired($course)) {
            return $this->buildResult('course.expired');
        }

        return true;
    }

    private function isExpired($course)
    {
        $expiryMode = $course['expiryMode'];
        if ($expiryMode === 'forever') {
            return false;
        }
        if ($expiryMode === 'date' || $expiryMode === 'end_date') {
            return time() > $course['expiryEndDate'];
        }

        return false;
    }
}
