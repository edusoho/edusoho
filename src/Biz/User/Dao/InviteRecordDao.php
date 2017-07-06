<?php

namespace Biz\User\Dao;

interface InviteRecordDao
{
    public function findByInviteUserId($userId);

    public function getByInvitedUserId($invitedUserId);

    public function findByInvitedUserIds($invitedUserIds);

    public function updateByInvitedUserId($invitedUserId, $fields);
}
