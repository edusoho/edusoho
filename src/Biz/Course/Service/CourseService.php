<?php

namespace Biz\Course\Service;

interface CourseService
{
    public function getCourse($id);

    public function findCoursesByCourseId($courseId);

    public function createCourse($course);

    public function updateCourse($id, $fields);

    public function copyCourse($copyId, $course);

    public function deleteCourse($id);

    public function closeCourse($id);

    public function saveCourseMarketing($courseMarketing);

    public function preparePublishment($id, $userId);

    public function auditPublishment($id, $userId, $reject, $remark);
}
