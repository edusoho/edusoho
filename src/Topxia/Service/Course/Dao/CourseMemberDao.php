<?php

namespace Topxia\Service\Course\Dao;

interface CourseMemberDao
{

    public function getMember($id);

    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function getMembersByCourseIds($courseIds);

    public function findMembersByUserIdAndRole($userId, $role, $start, $limit, $onlyPublished = true);

    public function findMemberCountByUserIdAndRole($userId, $role, $onlyPublished = true);

    public function findMemberCountByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned);

    public function findMembersByUserIdAndCourseTypeAndIsLearned($userId, $role, $type, $isLearned, $start, $limit);

    public function findMemberCountByUserIdAndRoleAndIsLearned($userId, $role, $isLearned);
    
    public function findMembersByUserIdAndRoleAndIsLearned($userId, $role, $isLearned, $start, $limit);
    
    public function findMembersByCourseIdAndRole($courseId, $role, $start, $limit);

    public function findMemberCountByCourseIdAndRole($courseId, $role);

    public function searchMemberCount($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);
    
    public function searchMember($conditions, $start, $limit);

    public function countMembersByStartTimeAndEndTime($startTime,$endTime);
        
    public function searchMemberIds($conditions, $orderBy, $start, $limit);

    public function addMember($member);

    public function updateMember($id, $member);

    public function deleteMember($id);

    public function deleteMembersByCourseId($courseId);

    public function deleteMemberByCourseIdAndUserId($courseId, $userId);

    public function findCourseMembersByUserId($userId);

    public function findLearnedCoursesByCourseIdAndUserId($courseId,$userId);

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds);
    
}