<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\CourseException;

class LearnCourseAccessor extends AccessorAdapter
{
    public function access($course)
    {
        if (empty($course)) {
            return $this->buildResult('NOTFOUND_COURSE', array(), CourseException::EXCEPTION_MODUAL);
        }

        if ($course['status'] === 'draft') {
            return $this->buildResult('UNPUBLISHED_COURSE', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }

        if ($this->isExpired($course)) {
            return $this->buildResult('EXPIRED_COURSE', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
        }

        if ($this->isNotArriving($course)) {
            return $this->buildResult('UN_ARRIVE', array('courseId' => $course['id']), CourseException::EXCEPTION_MODUAL);
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
