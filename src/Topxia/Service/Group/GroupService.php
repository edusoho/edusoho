<?php

namespace Topxia\Service\Group;

interface GroupService
{
    public function getGroup($id);

    public function getGroupsByIds($ids);

    public function searchGroups($conditions,$orderBy,$start,$limit);

    public function searchGroupsCount($condtions);

    public function updateGroup($id, $fields);

    public function addGroup($group);

    public function closeGroup($id);

    public function openGroup($id);

    public function changeGroupLogo($id, $pictureFilePath, $options);

    public function changeGroupBackgroundLogo($id, $pictureFilePath, $options);

    public function joinGroup($id);

    public function exitGroup($id);

    public function findGroupsByUserId($userId);

    public function findGroupsByTitle($title);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function searchMembersCount();

    public function isOwner($id,$userId);

    public function isMember($id, $userId);

    public function getMembersCountByGroupId($groupId);

    public function getMemberByGroupIdAndUserId($groupId,$userId);

    public function deleteMemberByGroupIdAndUserId($groupId, $userId);

    public function waveGroup($id,$field, $diff);

    public function waveMember($groupId,$userId,$field,$diff);

}