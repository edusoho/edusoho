<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomMemberDao
{   
    public function findClassroomMemberByClassIdAndUserIdAndRole($classroomId,$studentId,$role);

    public function getMember($id);

    public function getClassroomStudentCount($classroomId);

    public function searchMemberCount($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function getMemberByClassroomIdAndUserId($classroomId, $userId);

    public function updateMember($id, $member);

    public function deleteMember($id);
    
    public function deleteMemberByClassroomIdAndUserId($classroomId, $userId);

    public function addMember($member);

    public function findMemberCountByClassroomIdAndRole($classroomId, $role);
}
