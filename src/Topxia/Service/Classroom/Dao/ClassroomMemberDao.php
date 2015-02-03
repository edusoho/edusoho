<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomMemberDao
{   
    public function findClassroomMemberByClassIdAndUserIdAndRole($classroomId,$studentId,$role);

    public function addMember($member);

    public function getMember($id);

}