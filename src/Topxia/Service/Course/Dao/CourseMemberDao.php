<?php

namespace Topxia\Service\Course\Dao;

interface CourseMemberDao
{

    public function getMember($id);

    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function getMemberCountByUserId($userId);

    public function findMembersByUserId($userId, $start, $limit);
    
    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit);

    public function findMembersByUserIdAndRole($userId, $role, $start, $limit);

    public function findMemberCountByCourseIdAndRole($courseId, $role);

    public function findMembersByRole($role, $start, $limit);

    public function getMembersCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned);
    
    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit);

    public function addMember($member);

    public function updateMember($id, $member);

    public function deleteMember($id);

    public function deleteMembersByCourseId($courseId);

    public function deleteMemberByCourseIdAndUserId($courseId, $userId);
}