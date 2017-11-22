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

    public function deleteByTypeAndToId($type, $toId)
    {
        return $this->db()->delete($this->table, array('type' => $type, 'toId' => $toId));
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
