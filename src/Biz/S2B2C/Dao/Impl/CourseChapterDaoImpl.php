<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\Course\Dao\Impl\CourseChapterDaoImpl as BaseCourseChapterDaoImpl;
use Biz\S2B2C\Dao\CourseChapterDao;

class CourseChapterDaoImpl extends BaseCourseChapterDaoImpl implements CourseChapterDao
{
    public function getByCourseIdAndSyncId($courseId, $syncId)
    {
        return $this->getByFields([
            'courseId' => $courseId,
            'syncId' => $syncId,
        ]);
    }
}
