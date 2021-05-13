<?php

namespace Biz\ClassCourse\Service\Impl;

use Biz\BaseService;
use Biz\ClassCourse\Dao\ClassCourseProductDao;
use Biz\ClassCourse\Service\ClassCourseProductService;

class ClassCourseProductServiceImpl extends BaseService implements ClassCourseProductService
{
    /**
     * @return ClassCourseProductDao
     */
    protected function getClassCourseProductDao()
    {
        return $this->createDao('ClassCourse:ClassCourseProductDao');
    }
}
