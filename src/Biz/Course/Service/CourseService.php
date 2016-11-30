<?php

namespace Biz\Course\Service;

interface CourseService
{
    public function getCourse($id);

    public function findCoursesByCourseSetId($courseSetId);

    public function getDefaultCourseByCourseSetId($courseSetId);

    public function createCourse($course);

    public function updateCourse($id, $fields);

    public function updateCourseMarketing($id, $fields);

    public function deleteCourse($id);

    public function closeCourse($id);

    public function publishCourse($id, $userId);

    public function getCourseItems($courseId);

    public function tryManageCourse($courseId);

    public function getNextNumberAndParentId($courseId);

    public function tryTakeCourse($courseId);

    public function isCourseStudent($courseId, $userId);
}
