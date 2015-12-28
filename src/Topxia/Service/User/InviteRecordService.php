<?php
namespace Topxia\Service\User;

interface InviteRecordService
{
    public function createInviteRecord($inviteUserId, $invitedUserId);

    public function findRecordsByInviteUserId($userId); //找到有邀请码的用户,

    public function getRecordByInvitedUserId($invitedUserId);

    public function addInviteRewardRecordToInvitedUser($invitedUserId, $fields);

    public function addInviteRewardRecordToInviteUser($invitedUserId, $fields);

}
