<?php

namespace Biz\S2B2C\Dao;

use Biz\Course\Dao\CourseChapterDao as BaseCourseChapterDao;

interface CourseChapterDao extends BaseCourseChapterDao
{
    public function getByCourseIdAndSyncId($courseId, $syncId);
}
