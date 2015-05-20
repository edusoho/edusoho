<?php

namespace Classroom\Service\Classroom\Dao;

interface ClassroomCourseDao
{
    public function addCourse($course);

    public function update($id, $fields);

    public function updateByParam($params, $fields);

    public function deleteCourseByClassroomIdAndCourseId($classroomId, $courseId);

    public function getCourseByClassroomIdAndCourseId($classroomId, $courseId);

    public function searchCourses($conditions, $orderBy, $start, $limit);

    public function deleteCoursesByClassroomId($classroomId);

    public function findClassroomsByCourseId($courseId);

    public function findClassroomByCourseId($courseId);

    public function findCoursesByClassroomIdAndCourseIds($classroomId, $courseIds);

    public function findCoursesByClassroomId($classroomId);

    public function findActiveCoursesByClassroomId($classroomId);

    public function findCoursesByCoursesIds($courseIds);
}
