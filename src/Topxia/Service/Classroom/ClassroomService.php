<?php

namespace Topxia\Service\Classroom;

interface ClassroomService
{   
    public function getClassroom($id);

    public function updateClassroom($id,$fields);

    public function tryManageClassroom($id);
    
    public function deleteClassroom($id);
    
    public function searchClassrooms($conditions,$orderBy,$start,$limit);

    public function searchClassroomsCount($condtions);

    public function addClassroom($classroom);
    
    public function findClassroomByTitle($title);

    public function closeClassroom($id);

    public function publishClassroom($id);

    public function updateClassroomTeachers($id);

    public function changePicture ($id, $filePath, array $options);

    public function addCourse($classroomId,$courseId);

    public function getCourseByClassroomIdAndCourseId($classroomId,$courseId);

    public function getAllCourses($classroomId);

    public function updateCourses($classroomId,array $courseIds);

    public function isClassroomStudent($classroomId, $studentId);

    public function findCoursesByIds(array $ids);

    public function searchMemberCount($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function getClassroomMember($classroomId, $userId);

    public function remarkStudent($classroomId, $userId, $remark);

    public function removeStudent($classroomId, $userId);

    public function becomeStudent($classroomId, $userId);

    public function isClassroomTeacher($classroomId, $userId);

    public function getClassroomRole($classroomId,$userId);
    
}