<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonViewedDao;

class LessonViewedDaoImpl extends BaseDao implements LessonViewedDao
{
    protected $table = 'course_lesson_viewed';

    public function deleteViewedsByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->delete($this->table, 'course_lesson_viewed')
            ->where("courseId = :courseId")
            ->setParameter(":courseId", $courseId)
            ->execute();
    }

}