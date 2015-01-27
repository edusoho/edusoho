<?php

namespace Topxia\Service\Classroom;

interface ClassroomService

{   
    public function getClassroom($id);
    
    public function searchClassrooms($conditions,$orderBy,$start,$limit);

    public function searchClassroomsCount($condtions);

    public function addClassroom($classroom);

    public function canTakeClassroom($classroom);

    public function tryTakeClassroom($classId);
    
    public function getClassroomMember($classId, $userId);

    public function canManageClassroom($targetId);

}