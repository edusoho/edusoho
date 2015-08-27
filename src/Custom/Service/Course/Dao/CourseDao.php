<?php

namespace Custom\Service\Course\Dao;

interface CourseDao
{

    public function getPeriodicCoursesCount($rootId);

    /**
     * 获取课程相关的其它期课程.
     * @param course 课程对象.
     * @return 相关课程列表.
    **/
    public function findOtherPeriods($course);
}