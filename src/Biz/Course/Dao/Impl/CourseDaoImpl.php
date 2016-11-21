<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseDao;

class CourseDaoImpl extends SoftDeleteDaoImpl implements CourseDao
{
    protected $table = 'c2_course';

    public function declares()
    {
    }
}
