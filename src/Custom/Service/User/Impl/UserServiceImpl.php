<?php

namespace Custom\Service\User\Impl;

use Topxia\Service\User\Impl\UserServiceImpl as BaseUserServiceImpl;

class UserServiceImpl extends BaseUserServiceImpl
{
    public function findUsersByOrgCode($orgCode)
    {
        return $this->getUserDao()->findUsersByOrgCode($orgCode);
    }
    
    public function findCenterOrSuperAdminUsersByOrgId($orgId)
    {
        return $this->getUserDao()->findCenterAdminUsersByOrgId($orgId);
    }
}