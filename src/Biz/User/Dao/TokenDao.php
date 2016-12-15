<?php

namespace Biz\User\Dao;

interface TokenDao
{
    public function get($id, $lock = false);

    public function getByToken($token);

    public function create($token);

    public function findByUserIdAndType($userId, $type);

    public function getByType($type);

    public function deleteTopsByExpiredTime($expiredTime, $limit);
}
