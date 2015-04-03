<?php

namespace Custom\Service\User\Dao\Impl;
use Custom\Service\User\Dao\AllUserDao;
use Topxia\Service\Common\BaseDao;
use PDO;

class AllUserDaoImpl extends BaseDao implements AllUserDao
{
    protected $table = 'user';

    public function getAllUser()
    {
        $sql = "SELECT id FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql) ? : null;
    }

}