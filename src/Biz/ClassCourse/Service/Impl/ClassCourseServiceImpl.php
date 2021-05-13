<?php

namespace Biz\ClassCourse\Service\Impl;

use Biz\BaseService;
use Biz\ClassCourse\Dao\ClassCourseDao;
use Biz\ClassCourse\Service\ClassCourseService;

class ClassCourseServiceImpl extends BaseService implements ClassCourseService
{
    /**
     * @return ClassCourseDao
     */
    protected function getClassCourseDao()
    {
        return $this->createDao('ClassCourse:ClassCourseDao');
    }
}
