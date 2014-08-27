<?php

namespace Topxia\Service\Group;

interface GroupService
{
    public function getGroup($id);

    public function getGroupsByIds($ids);

    public function searchGroups($conditions,$orderBy,$start,$limit);

    public function searchGroupsCount($condtions);

    public function updateGroup($id, $fields);

    public function addGroup($user,$group);

    public function closeGroup($id);

    public function openGroup($id);

    public function changeGroupLogo($id, $pictureFilePath, $options);

    public function changeGroupBackgroundLogo($id, $pictureFilePath, $options);

    public function joinGroup($user,$id);

    public function exitGroup($user,$id);

    public function findGroupsByUserId($userId);

    public function findGroupByTitle($title);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function searchMembersCount($conditions);

    public function isOwner($id,$userId);

    public function isAdmin($id,$userId);

    public function isMember($id, $userId);

    public function addOwner($groupId,$userId);

    public function updateMember($id, $fields);

    public function getMembersCountByGroupId($groupId);

    public function getMemberByGroupIdAndUserId($groupId,$userId);

    public function deleteMemberByGroupIdAndUserId($groupId, $userId);

    public function waveGroup($id,$field, $diff);

    public function waveMember($groupId,$userId,$field,$diff);

}