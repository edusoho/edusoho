<?php

namespace Biz\RewardPoint\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Biz\RewardPoint\Dao\AccountDao;

class AccountDaoImpl extends AdvancedDaoImpl implements AccountDao
{
    protected $table = 'reward_point_account';

    public function deleteByUserId($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE userId = ?";

        return $this->db()->executeUpdate($sql, array($userId));
    }

    public function getByUserId($userId, $potions = array())
    {
        $lock = isset($options['lock']) && true === $options['lock'];

        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        if ($lock) {
            $sql .= ' FOR UPDATE';
        }

        return $this->db()->fetchAssoc($sql, array($userId));
    }

    public function waveBalance($id, $value)
    {
        $sql = "UPDATE {$this->table} SET balance = balance + ? WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, array($value, $id));
    }

    public function waveDownBalance($id, $value)
    {
        $sql = "UPDATE {$this->table} SET balance = balance - ? WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, array($value, $id));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime', 'balance', 'updatedTime'),
            'conditions' => array(
                'userId = :userId',
                'userId IN ( :userIds)',
            ),
        );
    }
}
