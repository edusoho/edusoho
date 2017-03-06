<?php

namespace Biz\User\Dao;

interface InviteRecordDao
{
    public function findByInviteUserId($userId);

    public function getByInvitedUserId($invitedUserId);

    public function updateByInvitedUserId($invitedUserId, $fields);
}
