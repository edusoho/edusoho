<?php

namespace Topxia\Service\Activity\Dao;

interface MemberDao
{

	 public function searchMemberCount($conditions);
    
    public function searchMember($conditions, $start, $limit);

    public function getMember($id);

    public function getMemberByActivityIdAndUserId($activityId, $userId);

    public function getMemberCountByUserId($userId);

    public function findMembersByUserId($userId, $start, $limit);
    
    public function findMembersByActivityId($activityId, $start, $limit);

    public function findMembersByUserIdAndRole($userId, $start, $limit);

    public function findMemberCountByActivityIdAndRole($activityId);

    public function findMembersByRole($start, $limit);

    public function getMembersCountByUserIdAndRoleAndIsLearned($userId, $isLearned);
    
    public function findMembersByUserIdAndRoleAndIsLearned($userId, $isLearned, $start, $limit);

    public function addMember($member);

    public function updateMember($id, $member);

    public function deleteMember($id);

    public function deleteMembersByActivityId($activityId);

    public function deleteMemberByActivityIdAndUserId($activityId, $userId);
}