<?php

namespace Biz\Course\Service;

interface CourseSetService
{
    public function tryManageCourseSet($id);

    public function getCourseSet($id);

    public function createCourseSet($courseSet);

    public function updateCourseSet($id, $fields);

    public function updateCourseSetDetail($id, $fields);

    public function changeCourseSetCover($id, $fields);

    public function deleteCourseSet($id);

    public function publishCourseSet($id);

    public function closeCourseSet($id);
}
