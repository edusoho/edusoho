<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserCommonAdminDao;

class UserCommonAdminDaoImpl extends BaseDao implements UserCommonAdminDao
{
    protected $table = 'shortcut';

    public function getCommonAdmin($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function findCommonAdminByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? order by id desc ";

        return $this->getConnection()->fetchAll($sql, array($userId)) ?: null;
    }

    public function getCommonAdminByUserIdAndUrl($userId, $url)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND url = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($userId, $url)) ?: null;
    }

    public function addCommonAdmin($admin)
    {
        $affected = $this->getConnection()->insert($this->table, $admin);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert common_admin error.');
        }

        return $this->getCommonAdmin($this->getConnection()->lastInsertId());
    }

    public function deleteCommonAdmin($id)
    {
        $this->getConnection()->delete($this->table, array('id' => $id));
    }
}
