<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserBindDao;

class UserBindDaoImpl extends BaseDao implements UserBindDao
{

    protected $table = 'user_bind';

    public function getBind($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function getBindByFromId($fromId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($fromId)) ? : array();
    }

    public function getBindByTypeAndFromId($type, $fromId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND fromId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($type, $fromId));
    }

    public function getBindByToIdAndType($type, $toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND toId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($type, $toId));
    }

    public function getBindByToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($token));
    }
    
    public function addBind($bind)
    {
        $affected = $this->getConnection()->insert($this->table, $bind);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert bind error.');
        }
        return $this->getBind($this->getConnection()->lastInsertId());
    }

    public function deleteBind($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function findBindsByToId($toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($toId));
    }

}