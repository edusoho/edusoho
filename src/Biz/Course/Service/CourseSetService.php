<?php

namespace Biz\Course\Service;

interface CourseSetService
{
    public function getCourseSet($id);

    public function createCourseSet($courseSet);

    public function updateCourseSet($id, $fields);

    public function deleteCourseSet($id);
}
