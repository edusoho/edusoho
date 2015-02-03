<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomMemberDao
{   
    public function findClassroomMemberByClassIdAndUserIdAndRole($classroomId,$studentId,$role);
 
}