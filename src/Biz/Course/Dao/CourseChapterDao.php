<?php

namespace Biz\Course\Dao;

interface CourseChapterDao
{
    public function findChaptersByCourseId($courseId);
}
