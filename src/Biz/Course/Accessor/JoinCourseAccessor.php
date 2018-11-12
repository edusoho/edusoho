<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\CourseException;

class JoinCourseAccessor extends AccessorAdapter
{
    public function access($course)
    {
        if (empty($course)) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'NOTFOUND_COURSE');
        }

        if ('draft' === $course['status']) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'UNPUBLISHED_COURSE', array('courseId' => $course['id']));
        }

        if ('closed' === $course['status']) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'CLOSED_COURSE', array('courseId' => $course['id']));
        }

        if (!$course['buyable']) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'UNBUYABLE_COURSE', array('courseId' => $course['id']));
        }

        if ($this->isExpired($course)) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'EXPIRED_COURSE', array('courseId' => $course['id']));
        }
        if ($course['buyExpiryTime'] && time() > $course['buyExpiryTime']) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'BUY_EXPIRED', array('courseId' => $course['id']));
        }

        if ($course['maxStudentNum'] && $course['maxStudentNum'] <= $course['studentNum']) {
            return $this->buildResult(CourseException::EXCEPTION_MODUAL, 'REACH_MAX_STUDENT', array('courseId' => $course['id']));
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
