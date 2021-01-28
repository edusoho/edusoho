<?php

namespace Biz\Course\Service;

interface CourseDeleteService
{
    /**
     * 删除课程及相关信息.
     *
     * @param $courseSetId
     */
    public function deleteCourseSet($courseSetId);

    /**
     * 删除教学计划及相关信息.
     *
     * @param  $courseId
     *
     * @return mixed
     */
    public function deleteCourse($courseId);
}
