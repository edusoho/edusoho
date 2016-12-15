<?php

namespace Biz\User\Dao;

interface NotificationDao
{
    public function searchByUserId($userId, $start, $limit);
}
