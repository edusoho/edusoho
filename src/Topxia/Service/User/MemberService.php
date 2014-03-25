<?php
namespace Topxia\Service\User;

interface MemberService
{
    public function getMemberByUserId($userId);

    public function checkMemberName($memberName);

    public function searchMembers(array $conditions, array $orderBy, $start, $limit);

    public function searchMembersCount($conditions);

    public function createMember($memberDate);

    public function updateMemberInfo($userId, array $fields);

    public function cancelMemberByUserId($userId);

    public function searchMembersHistoriesCount($conditions);

    public function searchMembersHistories(array $conditions, array $orderBy, $start, $limit);
}