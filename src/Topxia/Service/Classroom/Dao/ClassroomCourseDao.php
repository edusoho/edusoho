<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomCourseDao
{   
    public function addCourse($course);

    public function getCourseByClassroomIdAndCourseId($classroomId,$courseId);

    public function searchCourses($conditions,$orderBy,$start,$limit);

    public function deleteCoursesByClassroomId($classroomId);
}