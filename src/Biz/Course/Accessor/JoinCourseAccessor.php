<?php

namespace Biz\Course\Accessor;

class JoinCourseAccessor extends AccessorAdapter implements AccessorInterface
{
    public function access($course)
    {
        if ($course['status'] === 'draft') {
            return $this->buildResult('course.unpublished');
        }

        if ($course['status'] === 'closed') {
            return $this->buildResult('course.closed');
        }

        if ($this->isExpiried($course)) {
            return $this->buildResult('course.expiried');
        }
        if ($course['buyExpiryTime'] && time() > $course['buyExpiryTime']) {
            return $this->buildResult('course.buy_expiried');
        }

        return true;
    }

    private function isExpiried($course)
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
