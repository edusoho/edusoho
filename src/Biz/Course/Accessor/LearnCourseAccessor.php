<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;

class LearnCourseAccessor extends AccessorAdapter
{
    public function access($course)
    {
        if (empty($course)) {
            return $this->buildResult('course.not_found');
        }

        if ($course['status'] === 'draft') {
            return $this->buildResult('course.unpublished', array('courseId' => $course['id']));
        }

        if ($this->isExpired($course)) {
            return $this->buildResult('course.expired', array('courseId' => $course['id']));
        }

        if ($this->isNotArriving($course)) {
            return $this->buildResult('course.not_arrive', array('courseId' => $course['id']));
        }

        return null;
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

    private function isNotArriving($course)
    {
        return $course['expiryMode'] == 'date' and $course['expiryStartDate'] > time();
    }
}
