<?php

namespace Custom\Service\User\Impl;

use Topxia\Common\ArrayToolkit;
use Custom\Service\User\UserService;
use Topxia\Service\User\Impl\UserServiceImpl as BaseUserServiceImpl;

class UserServiceImpl extends BaseUserServiceImpl implements UserService
{
    public function findUsersByOrgCode($orgCode)
    {
        return $this->getUserDao()->findUsersByOrgCode($orgCode);
    }

    public function getUserDao()
    {
        return $this->createDao('Custom:User.UserDao');
    }
}