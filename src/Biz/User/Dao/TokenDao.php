<?php

namespace Biz\User\Dao;

interface TokenDao
{
    public function getByToken($token);

    public function waveRemainedTimes($id, $diff);

    public function deleteByExpiredTime($expiredTime, $limit);

    public function findByUserIdAndType($userId, $type);

    public function getByType($type);
}
