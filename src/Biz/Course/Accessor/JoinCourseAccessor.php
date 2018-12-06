<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\CourseException;

class JoinCourseAccessor extends AccessorAdapter
{
    public function access($course)
    {
        if (empty($course)) {
            return $this->buildResult('NOTFOUND_COURSE', array(), CourseException::EXCEPTION_MODUAL);
        }

        if ('draft' === $course['status']) {
            return $this->buildResult('UNPUBLISHED_COURSE', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }

        if ('closed' === $course['status']) {
            return $this->buildResult('CLOSED_COURSE', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }

        if (!$course['buyable']) {
            return $this->buildResult('UNBUYABLE_COURSE', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }

        if ($this->isExpired($course)) {
            return $this->buildResult('EXPIRED_COURSE', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }
        if ($course['buyExpiryTime'] && time() > $course['buyExpiryTime']) {
            return $this->buildResult('BUY_EXPIRED', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }

        if ($course['maxStudentNum'] && $course['maxStudentNum'] <= $course['studentNum']) {
            return $this->buildResult('REACH_MAX_STUDENT', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }

        return null;
    }

    private function isExpired($course)
    {
        $expiryMode = $course['expiryMode'];
        if ('forever' === $expiryMode) {
            return false;
        }
        if ('date' === $expiryMode || 'end_date' === $expiryMode) {
            return time() > $course['expiryEndDate'];
        }

        return false;
    }
}
