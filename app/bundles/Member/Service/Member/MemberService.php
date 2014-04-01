<?php
namespace Member\Service\Member;

interface MemberService
{
    public function getMemberByUserId($userId);

    public function checkMemberName($memberName);

    public function searchMembers(array $conditions, array $orderBy, $start, $limit);

    public function searchMembersCount($conditions);

    public function createMember($memberData);

    /**
     * 加入会员
     */
    public function becomeMember($userId, $levelId, $duration, $unit, $orderId = 0);

    /**
     * 续费会员
     */
    public function renewMember($userId, $duration, $unit, $orderId = 0);

    /**
     * 升级会员
     */
    public function upgradeMember($userId, $newLevelId, $orderId = 0);

    /**
     * 计算升级会员，所需要的金额
     */
    public function calUpgradeMemberAmount($userId, $newLevelId);

    public function updateMemberInfo($userId, array $fields);

    public function cancelMemberByUserId($userId);

    public function searchMembersHistoriesCount($conditions);

    public function searchMembersHistories(array $conditions, array $orderBy, $start, $limit);

    /**
     * 检查用户是否有某等级的权限
     */
    public function checkUserInMemberLevel($userId, $levelId);
}