<?php

namespace Biz\User\Dao;

interface BlacklistDao
{
    public function getByUserIdAndBlackId($userId, $blackId);

    public function findByUserId($userId);

    public function deleteByUserIdAndBlackId($userId, $blackId);
}
