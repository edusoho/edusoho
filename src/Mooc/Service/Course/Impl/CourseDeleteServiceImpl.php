<?php


namespace Mooc\Service\Course\Impl;

use Topxia\Service\Course\Impl\CourseDeleteServiceImpl as BaseCourseDeleteService;

class CourseDeleteServiceImpl extends BaseCourseDeleteService
{
    protected function deleteCourse($course)
    {
        parent::deleteCourse($course);
        if ('periodic' == $course['type']) {
            $this->getCourseDao()->subPeriodsByRootId($course['rootId'], $course['periods']);
        }
        return 0;
    }
}