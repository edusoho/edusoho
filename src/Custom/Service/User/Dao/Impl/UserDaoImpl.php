<?php

namespace Custom\Service\User\Dao\Impl;

use Custom\Service\User\Dao\UserDao;
use Topxia\Service\User\Dao\Impl\UserDaoImpl as BaseUserDaoImpl;

class UserDaoImpl extends BaseUserDaoImpl implements UserDao
{
    protected $table = 'user';

    public function findUsersByOrgCode($orgCode)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE orgCode LIKE '%{$orgCode}%'";

        return $this->getConnection()->fetchAll($sql, array($orgCode)) ? : array();
    }
}