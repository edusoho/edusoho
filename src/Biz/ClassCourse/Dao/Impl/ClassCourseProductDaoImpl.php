<?php

namespace Biz\ClassCourse\Dao\Impl;

use Biz\ClassCourse\Dao\ClassCourseProductDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassCourseProductDaoImpl extends GeneralDaoImpl implements ClassCourseProductDao
{
    protected $table = 'class_course_product';

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
