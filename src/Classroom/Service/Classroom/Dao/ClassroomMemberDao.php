<?php

namespace Classroom\Service\Classroom\Dao;

interface ClassroomMemberDao
{
    public function getMember($id);

    public function getClassroomStudentCount($classroomId);

    public function getClassroomAuditorCount($classroomId);

    public function searchMemberCount($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function getMemberByClassroomIdAndUserId($classroomId, $userId);

    public function updateMember($id, $member);

    public function deleteMember($id);

    public function deleteMemberByClassroomIdAndUserId($classroomId, $userId);

    public function addMember($member);

    public function findAssistants($classroomId);

    public function findMembersByUserIdAndClassroomIds($userId, array $classroomIds);

    public function findMembersByClassroomIdAndRole($classroomId, $role, $start, $limit);

    public function findMembersByClassroomIdAndUserIds($classroomId, $userIds);

    public function findMobileVerifiedMemberCountByClassroomId($classroomId, $locked);

    public function findMemberUserIdsByClassroomId($classroomId);
}
