<?php

namespace Biz\User\Dao;

interface InviteRecordDao
{
    public function findByInviteUserId($userId);

    public function getByInvitedUserId($invitedUserId);

    public function findByInvitedUserIds($invitedUserIds);

    public function updateByInvitedUserId($invitedUserId, $fields);

    public function findByInviteUserIds($userIds);

    public function sumCouponRateByInviteUserId($userId);

    public function searchRecordGroupByInviteUserId($conditions, $start, $limit);

    public function countInviteUser($conditions);

    // 根据邀请者ids，查找被邀请者中付费的用户数
    public function countPremiumUserByInviteUserIds($userIds);
}
