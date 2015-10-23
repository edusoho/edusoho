<?php

namespace Topxia\Service\Course\Dao;

interface LessonViewedDao
{

    public function deleteViewedsByCourseId($courseId);
}