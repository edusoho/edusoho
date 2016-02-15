<?php

namespace Topxia\Service\User\Dao;

interface BlacklistDao
{
    public function getBlacklist($id);

    public function getBlacklistByUserIdAndBlackId($userId, $blackId);
    
    public function findBlacklistsByUserId($userId);
    
    public function addBlacklist($blacklist);

    public function deleteBlacklistByUserIdAndBlackId($userId, $blackId);

}

