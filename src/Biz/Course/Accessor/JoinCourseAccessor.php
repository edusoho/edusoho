<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;

class JoinCourseAccessor extends AccessorAdapter
{
    public function access($course)
    {
        if (empty($course)) {
            return $this->buildResult('course.not_found');
        }

        if ('draft' === $course['status']) {
            return $this->buildResult('course.unpublished', array('courseId' => $course['id']));
        }

        if ('closed' === $course['status']) {
            return $this->buildResult('course.closed', array('courseId' => $course['id']));
        }

        if (!$course['buyable']) {
            return $this->buildResult('course.not_buyable', array('courseId' => $course['id']));
        }

        if ($this->isExpired($course)) {
            return $this->buildResult('course.expired', array('courseId' => $course['id']));
        }
        if ($course['buyExpiryTime'] && time() > $course['buyExpiryTime']) {
            return $this->buildResult('course.buy_expired', array('courseId' => $course['id']));
        }

        if ($course['maxStudentNum'] && $course['maxStudentNum'] <= $course['studentNum']) {
            return $this->buildResult('course.reach_max_student_num', array('courseId' => $course['id']));
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
