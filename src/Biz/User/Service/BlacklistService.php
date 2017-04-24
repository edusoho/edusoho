<?php

namespace Biz\User\Service;

interface BlacklistService
{
    public function getBlacklist($id);

    public function getBlacklistByUserIdAndBlackId($userId, $blackId);

    public function findBlacklistsByUserId($userId);

    public function addBlacklist($blacklist);

    public function deleteBlacklistByUserIdAndBlackId($userId, $blackId);

    public function canTakeBlacklist($userId);
}
