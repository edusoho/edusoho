<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\TokenDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class TokenDaoImpl extends AdvancedDaoImpl implements TokenDao
{
    protected $table = 'user_token';

    public function get($id, array $options = [])
    {
        $lock = isset($options['lock']) && true === $options['lock'];
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$id]) ?: null;
    }

    public function getByToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$token]) ?: null;
    }

    public function findByTokens(array $tokens)
    {
        return $this->findInField('token', $tokens);
    }

    public function findByUserIdAndType($userId, $type)
    {
        return $this->findByFields(['userId' => $userId, 'type' => $type]);
    }

    public function destroyTokensByUserId($userId)
    {
        return $this->db()->delete($this->table, ['userId' => $userId]);
    }

    public function getByType($type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ?  and expiredTime > ? order  by createdTime DESC  LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$type, time()]) ?: null;
    }

    public function deleteTopsByExpiredTime($expiredTime, $limit)
    {
        $limit = (int) $limit;
        $sql = "DELETE FROM {$this->table} WHERE expiredTime < ? LIMIT {$limit} ";

        return $this->db()->executeQuery($sql, [$expiredTime]);
    }

    public function deleteByTypeAndUserId($type, $userId)
    {
        return $this->db()->delete($this->table, ['type' => $type, 'userId' => $userId]);
    }

    public function declares()
    {
        return [
            'conditions' => ['type = :type'],
            'serializes' => ['data' => 'php'],
            'timestamps' => ['createdTime'],
        ];
    }
}
