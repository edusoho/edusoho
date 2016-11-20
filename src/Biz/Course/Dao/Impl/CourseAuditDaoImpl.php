<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseAuditDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseAuditDaoImpl extends GeneralDaoImpl implements CourseAuditDao
{
    protected $table = 'c2_course_audit';

    public function declares()
    {
        return array(
            'timestamps' => array('created')
        );
    }
}
