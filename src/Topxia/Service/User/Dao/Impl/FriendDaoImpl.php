<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\FriendDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;
    
class FriendDaoImpl extends BaseDao implements FriendDao
{
    protected $table = 'friend';

    public function getFriend($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addFriend($friend)
    {
        $affected = $this->getConnection()->insert($this->table, $friend);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert friend error.');
        }
        return $this->getFriend($this->getConnection()->lastInsertId());
    }

    public function deleteFriend($id)
    {
       return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getFriendByFromIdAndToId($fromId, $toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? AND toId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($fromId, $toId));
    }

    public function findFriendsByFromId($fromId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($fromId));
    }

    public function findAllUserFollowingByFromId($fromId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? ORDER BY createdTime DESC ";
        return $this->getConnection()->fetchAll($sql, array($fromId));
    }

    public function findAllUserFollowerByToId($toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? ORDER BY createdTime DESC ";
        return $this->getConnection()->fetchAll($sql, array($toId));
    }

    public function findFriendCountByFromId($fromId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE fromId = ?";
        return $this->getConnection()->fetchColumn($sql, array($fromId));
    }

    public function findFriendsByToId($toId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($toId));
    }

    public function findFriendCountByToId($toId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE toId = ?";
        return $this->getConnection()->fetchColumn($sql, array($toId));
    }

    public function getFriendsByFromIdAndToIds($fromId, array $toIds)
    {
        if (empty($toIds)) { return array(); }
        $toIds = array_values($toIds);
        $marks = str_repeat('?,', count($toIds) - 1) . '?';
        $parmaters = array_merge(array($fromId), $toIds);
        $sql ="SELECT * FROM {$this->table} WHERE fromId = ? AND toId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $parmaters);
    }
}