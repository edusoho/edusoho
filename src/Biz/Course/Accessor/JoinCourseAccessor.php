<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\Course\Service\CourseSetService;
use Biz\MultiClass\Service\MultiClassService;

class JoinCourseAccessor extends AccessorAdapter
{
    public function access($course)
    {
        if (empty($course)) {
            return $this->buildResult('course.not_found');
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ('draft' === $course['status'] || 'draft' == $courseSet['status']) {
            return $this->buildResult('course.unpublished', ['courseId' => $course['id']]);
        }

        if ('closed' === $course['status'] || 'closed' == $courseSet['status']) {
            return $this->buildResult('course.closed', ['courseId' => $course['id']]);
        }

        if (!$course['buyable']) {
            return $this->buildResult('course.not_buyable', ['courseId' => $course['id']]);
        }

        if ($this->isExpired($course)) {
            return $this->buildResult('course.expired', ['courseId' => $course['id']]);
        }
        if ($course['buyExpiryTime'] && time() > $course['buyExpiryTime']) {
            return $this->buildResult('course.buy_expired', ['courseId' => $course['id']]);
        }

        if ($course['maxStudentNum'] && $course['maxStudentNum'] <= $course['studentNum']) {
            return $this->buildResult('course.reach_max_student_num', ['courseId' => $course['id']]);
        }

        $multiClass = $this->getMultiClassService()->getMultiClassByCourseId($course['id']);
        if (!empty($multiClass['maxStudentNum']) && $multiClass['maxStudentNum'] <= $course['studentNum']) {
            return $this->buildResult('course.reach_max_student_num', ['courseId' => $course['id']]);
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

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->biz->service('MultiClass:MultiClassService');
    }
}
