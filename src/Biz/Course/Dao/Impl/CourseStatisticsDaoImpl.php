<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseStatisticsDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseStatisticsDaoImpl extends GeneralDaoImpl implements CourseStatisticsDao
{
    protected $table = 'c2_course_statistics';

    public function declares()
    {
    }
}
