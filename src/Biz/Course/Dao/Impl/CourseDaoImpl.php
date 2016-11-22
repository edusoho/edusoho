<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDao;

class CourseDaoImpl extends SoftDeleteDaoImpl implements CourseDao
{
    protected $table = 'c2_course';

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->findInField('courseSetId', array($courseSetId));
    }

    public function findCoursesByTitle($title)
    {
        return $this->findInField('title', array($title));
    }

    public function declares()
    {
    }
}
