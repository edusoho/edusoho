<?php

namespace Biz\User\Service;

interface InviteRecordService
{
    public function createInviteRecord($inviteUserId, $invitedUserId);

    public function findRecordsByInviteUserId($userId);

 //找到有邀请码的用户,

    public function getRecordByInvitedUserId($invitedUserId);

    public function addInviteRewardRecordToInvitedUser($invitedUserId, $fields);

    public function countRecords($conditions);

    public function searchRecords($conditions, $orderBy, $start, $limit);

    public function findByInvitedUserIds($invitedUserIds);

    public function findByInviteUserIds($userIds);

    // 得到这个用户在注册后消费情况，订单消费总额；订单虚拟币总额；订单现金总额
    public function getUserOrderDataByUserIdAndTime($userId, $inviteTime);

    public function getAllUsersByRecords($records);
}
