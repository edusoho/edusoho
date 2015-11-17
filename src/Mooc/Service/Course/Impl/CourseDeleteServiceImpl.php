<?php


namespace Mooc\Service\Course\Impl;

use Topxia\Service\Course\Impl\CourseDeleteServiceImpl as BaseCourseDeleteService;

class CourseDeleteServiceImpl extends BaseCourseDeleteService
{
    protected function deleteCourse($course)
    {
        parent::deleteCourse($course);
        if ('periodic' == $course['type']) {
            $this->deleteCourseScore($course);
            $this->getCourseDao()->subPeriodsByRootId($course['rootId'], $course['periods']);
        }
        return 0;
    }

    protected function deleteCourseScore($course)
    {
        $this->getCourseScoreService()->deleteCourseScoreByCourseId($course['id']);
    }

    protected function getCourseScoreService()
    {
        return $this->createService('Mooc:Course.CourseScoreService');
    }
}