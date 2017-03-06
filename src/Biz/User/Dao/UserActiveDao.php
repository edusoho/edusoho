<?php

namespace Biz\User\Dao;

interface UserActiveDao
{
    public function getByUserId($userId);

    public function analysis($startTime, $endTime);
}
