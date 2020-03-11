<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserBindDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserBindDaoImpl extends GeneralDaoImpl implements UserBindDao
{
    protected $table = 'user_bind';

    public function getByFromId($fromId)
    {
        return $this->getByFields(array('fromId' => $fromId));
    }

    public function getByTypeAndFromId($type, $fromId)
    {
        return $this->getByFields(array('fromId' => $fromId, 'type' => $type));
    }

    public function getByToIdAndType($type, $toId)
    {
        return $this->getByFields(array('toId' => $toId, 'type' => $type));
    }

    public function getByToken($token)
    {
        return $this->getByFields(array('token' => $token));
    }

    public function findByToId($toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array($toId));
    }

    public function findByTypeAndFromIds($type, $fromIds)
    {
        if (empty($fromIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($fromIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND fromId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge(array($type), $fromIds)) ?: array();
    }

    public function findByTypeAndToIds($type, $toIds)
    {
        if (empty($toIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($toIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND toId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge(array($type), $toIds)) ?: array();
    }

    public function findByToIdAndType($type, $toId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE toId = ? AND type = ? ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array($toId, $type));
    }

    public function deleteByTypeAndToId($type, $toId)
    {
        return $this->db()->delete($this->table, array('type' => $type, 'toId' => $toId));
    }

    public function deleteByToId($toId)
    {
        return $this->db()->delete($this->table, array('toId' => $toId));
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'fromId = :fromId',
                'toId = :toId',
                'type = :type',
            ),
        );
    }
}
