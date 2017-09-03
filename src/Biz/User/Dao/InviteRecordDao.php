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

    public function countPremiumUserByInviteUserIds($userIds);
}
