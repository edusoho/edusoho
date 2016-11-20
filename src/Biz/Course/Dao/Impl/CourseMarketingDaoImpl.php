<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseMarketingDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseMarketingDaoImpl extends GeneralDaoImpl implements CourseMarketingDao
{
    protected $table = 'c2_course_marketing';

    public function declares()
    {
    }
}
