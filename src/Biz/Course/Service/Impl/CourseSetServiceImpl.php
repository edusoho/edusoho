<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseSetService;

class CourseSetServiceImpl extends BaseService implements CourseSetService
{
    public function getCourseSet($id)
    {
        return $this->getCourseSetDao()->get($id);
    }

    public function createCourseSet($courseSet)
    {
        //TODO validator

        //TODO  同时创建默认的教学计划

        return $this->getCourseSetDao()->create($course);
    }

    public function updateCourseSet($id, $fields)
    {
        //TODO validator

        return $this->getCourseSetDao()->update($id, $course);
    }

    public function deleteCourseSet($id)
    {
        return $this->getCourseSetDao()->delete($id);
    }

    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }
}
