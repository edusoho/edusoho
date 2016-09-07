<?php

namespace Custom\Service\User\Dao;

interface UserDao
{
    public function findUsersByOrgCode($orgCode);
}