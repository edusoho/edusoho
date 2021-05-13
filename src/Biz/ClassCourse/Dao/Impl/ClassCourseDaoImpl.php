<?php

namespace Biz\ClassCourse\Dao\Impl;

use Biz\ClassCourse\Dao\ClassCourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassCourseDaoImpl extends GeneralDaoImpl implements ClassCourseDao
{
    protected $table = 'class_course';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
