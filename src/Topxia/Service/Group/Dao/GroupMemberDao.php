<?php

namespace Topxia\Service\Group\Dao;

interface GroupMemberDao
{

 	public function getMembersCountByGroupId($groupId);

 	public function searchMembers($conditions,$orderBy,$start,$limit);

 	public function searchMembersCount($conditions);

 	public function getMemberByGroupIdAndUserId($groupId, $userId);

 	public function getMembersByUserId($userId);

 	public function getMember($id);

 	public function addMember($fields);

 	public function updateMember($id, $fields);
 
    public function deleteMember($id);

    public function waveMember($id, $field, $diff);
}