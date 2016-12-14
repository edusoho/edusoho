<?php

namespace Biz\User\Dao;

interface UserSecureQuestionDao
{
    public function findByUserId($userId);
}
