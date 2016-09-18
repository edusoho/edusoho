<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\BlacklistDao;

class BlacklistDaoImpl extends BaseDao implements BlacklistDao
{
    protected $table = 'blacklist';

    public function getBlacklist($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        });
    }

    public function getBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:blackId:{$blackId}", $userId, $blackId, function ($userId, $blackId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? AND blackId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($userId, $blackId)) ?: null;
        });
    }

    public function findBlacklistsByUserId($userId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}", $userId, function ($userId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? ";
            return $that->getConnection()->fetchAll($sql, array($userId)) ?: array();
        });
    }

    public function addBlacklist($blacklist)
    {
        $affected = $this->getConnection()->insert($this->table, $blacklist);
        $this->clearCached();
        if ($affected <= 0) {
            throw $this->createDaoException('Insert blacklist error.');
        }
        return $this->getBlacklist($this->getConnection()->lastInsertId());
    }

    public function deleteBlacklistByUserIdAndBlackId($userId, $blackId)
    {
        $result = $this->getConnection()->delete($this->table, array('userId' => $userId, 'blackId' => $blackId));
        $this->clearCached();
        return $result;
    }
}
