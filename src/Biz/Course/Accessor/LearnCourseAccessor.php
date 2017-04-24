<?php

namespace Biz\Course\Accessor;

class LearnCourseAccessor extends AccessorAdapter implements AccessorInterface
{
    public function access($course)
    {
        if ($course['status'] === 'draft') {
            return $this->buildResult('course.unpublished');
        }

        if ($this->isExpiried($course)) {
            return $this->buildResult('course.expiried');
        }

        return true;
    }
}
