<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\TokenDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TokenDaoImpl extends GeneralDaoImpl implements TokenDao
{
    protected $table = 'user_token';

    public function get($id, array $options = array())
    {
        $lock = isset($options['lock']) && $options['lock'] === true;
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getByToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($token)) ?: null;
    }

    public function findByUserIdAndType($userId, $type)
    {
        return $this->findByFields(array('userId' => $userId, 'type' => $type));
    }

    public function destroyTokensByUserId($userId)
    {
        return $this->db()->delete($this->table, array('userId' => $userId));
    }

    public function getByType($type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ?  and expiredTime > ? order  by createdTime DESC  LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($type, time())) ?: null;
    }

    public function deleteTopsByExpiredTime($expiredTime, $limit)
    {
        $limit = (int) $limit;
        $sql = "DELETE FROM {$this->table} WHERE expiredTime < ? LIMIT {$limit} ";

        return $this->db()->executeQuery($sql, array($expiredTime));
    }

    public function deleteByTypeAndUserId($type, $userId)
    {
        return $this->db()->delete($this->table, array('type' => $type, 'userId' => $userId));
    }

    public function declares()
    {
        return array(
            'conditions' => array('type = :type'),
            'serializes' => array('data' => 'php'),
        );
    }
}
