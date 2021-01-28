<?php

namespace Biz\User\Dao;

interface UserCommonAdminDao
{
    public function findByUserId($userId);

    public function getByUserIdAndUrl($userId, $url);
}
