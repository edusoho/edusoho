<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserRelationDao;
use Topxia\Common\DaoException;
use PDO;

class UserRelationDaoImpl extends BaseDao implements UserRelationDao
{
    protected $table = 'user_relation';

    public function getUserRelation($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getUserRelationByFromIdAndToIdAndType($fromId,$toId,$type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? and toId=? and type=?  LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($fromId,$toId,$type)) ? : null;
    }

    public function findUserRelationsByToIdsAndType(array $toIds,$type)
    {
        if(empty($toIds)){ return array(); }
        $marks = str_repeat('?,', count($toIds) - 1) . '?';
        $toIds[]=$type;
        $sql ="SELECT * FROM {$this->table} WHERE toId IN ({$marks}) and type=?;";
        return $this->getConnection()->fetchAll($sql, $toIds);
    }

    public function findUserRelationsByToIdAndType($toId,$type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? and type= ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($toId,$type));
    }

    public function findUserRelationsByFromIdAndType($fromId,$type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fromId = ? and type= ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($fromId,$type));
    }

    public function findUserRelationsByFromIdsAndType(array $fromIds,$type)
    {
        if(empty($fromIds)){ return array(); }
        $marks = str_repeat('?,', count($fromIds) - 1) . '?';
        $fromIds[]=$type;
        $sql ="SELECT * FROM {$this->table} WHERE fromId IN ({$marks}) and type=?;";
        return $this->getConnection()->fetchAll($sql, $fromIds);
    }

    public function addUserRelation($userRelation)
    {
        $affected = $this->getConnection()->insert($this->table, $userRelation);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert userRelation error.');
        }
        return $this->getUserRelation($this->getConnection()->lastInsertId());
    }

    public function deleteUserRelationsByFromIdAndType($fromId,$type)
    {
        return $this->getConnection()->delete($this->table, array('fromId' => $fromId,'type' => $type));
    }

}