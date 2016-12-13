<?php

namespace Biz\User\Dao;

interface NotificationDao
{
    public function findByUserId($userId, $start, $limit);

    public function countByUserId($userId);
}
