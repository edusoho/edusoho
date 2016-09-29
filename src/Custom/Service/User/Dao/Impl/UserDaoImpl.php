<?php

namespace Custom\Service\User\Dao\Impl;

use Topxia\Service\User\Dao\Impl\UserDaoImpl as BaseUserDaoImpl;

class UserDaoImpl extends BaseUserDaoImpl
{
    public function findUsersByOrgCode($orgCode)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE orgCode LIKE '%{$orgCode}%'";

        return $this->getConnection()->fetchAll($sql, array($orgCode)) ? : array();
    }
    
    public function findCenterAdminUsersByOrgId($orgId)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE orgId = '{$orgId}' and (roles LIKE '%|ROLE_CENTER_ADMIN|%' or roles LIKE '%|ROLE_SUPER_ADMIN|%')";
        
        return $this->getConnection()->fetchAll($sql, array($orgId)) ? : array();
    }
}