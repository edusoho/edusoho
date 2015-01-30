<?php

namespace Topxia\Service\Classroom;

interface ClassroomService

{   
    public function getClassroom($id);

    public function updateClassroom($id,$fields);
    
    public function searchClassrooms($conditions,$orderBy,$start,$limit);

    public function searchClassroomsCount($condtions);

    public function addClassroom($classroom);

    public function closeClassroom($id);

    public function publishClassroom($id);

    public function changePicture ($id, $filePath, array $options);

    public function addCourse($classroomId,$courseId);

    public function getCourseByClassroomIdAndCourseId($classroomId,$courseId);

    public function getAllCourses($classroomId);

}