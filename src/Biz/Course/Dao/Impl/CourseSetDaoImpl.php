<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseSetDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseSetDaoImpl extends GeneralDaoImpl implements CourseSetDao
{
    protected $table = 'c2_course_set';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(
                'tags'      => 'delimiter',
                'goals'     => 'delimiter',
                'audiences' => 'delimiter',
                'cover'     => 'json'
            )
        );
    }
}
