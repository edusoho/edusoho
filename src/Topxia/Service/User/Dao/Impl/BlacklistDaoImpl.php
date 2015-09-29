<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\BlacklistDao;
use Topxia\Common\DaoException;
use PDO;

class BlacklistDaoImpl extends BaseDao implements BlacklistDao
{
    protected $table = 'blacklist';

    public function getBlacklist($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND blackId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $blackId)) ? : null;
    }

    public function findBlacklistsByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ";
        return $this->getConnection()->fetchAll($sql, array($userId)) ? : array();
    }

    public function addBlacklist($blacklist)
    {
        $affected = $this->getConnection()->insert($this->table, $blacklist);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert blacklist error.');
        }
        return $this->getBlacklist($this->getConnection()->lastInsertId());
    }

    public function deleteBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        return $this->getConnection()->delete($this->table, array('userId' => $userId,'blackId' => $blackId));
    }

}