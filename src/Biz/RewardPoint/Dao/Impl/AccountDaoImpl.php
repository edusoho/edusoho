<?php

namespace Biz\RewardPoint\Dao\Impl;

use Biz\RewardPoint\Dao\AccountDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AccountDaoImpl extends AdvancedDaoImpl implements AccountDao
{
    protected $table = 'reward_point_account';

    public function deleteByUserId($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE userId = ?";

        return $this->db()->executeUpdate($sql, [$userId]);
    }

    public function getByUserId($userId, $options = [])
    {
        $lock = isset($options['lock']) && true === $options['lock'];

        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        if ($lock) {
            $sql .= ' FOR UPDATE';
        }

        return $this->db()->fetchAssoc($sql, [$userId]);
    }

    public function waveBalance($id, $value)
    {
        $sql = "UPDATE {$this->table} SET balance = balance + ? WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, [$value, $id]);
    }

    public function waveDownBalance($id, $value)
    {
        $sql = "UPDATE {$this->table} SET balance = balance - ? WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, [$value, $id]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'balance', 'updatedTime'],
            'conditions' => [
                'userId = :userId',
                'userId IN ( :userIds)',
            ],
        ];
    }
}
