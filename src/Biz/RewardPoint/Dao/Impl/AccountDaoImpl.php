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

    public function countJoinUser($conditions)
    {
        $sql = 'select count(*) from user u left join user_profile up on u.id=up.id left join reward_point_account rpa on u.id=rpa.userId where u.type <> "system"';
        $params = [];
        if (!empty($conditions['nickname'])) {
            $sql .= ' and u.nickname like ?';
            $params[] = "%{$conditions['nickname']}%";
        }
        if (!empty($conditions['truename'])) {
            $sql .= ' and up.truename like ?';
            $params[] = "%{$conditions['truename']}%";
        }

        return $this->db()->fetchColumn($sql, $params);
    }

    public function searchJoinUser($conditions, $orderBys, $start, $limit)
    {
        $sql = 'select u.id, u.nickname, u.verifiedMobile, u.email, up.truename, up.mobile, ifnull(rpa.balance, 0) as balance, ifnull(rpa.outflowAmount, 0) from user u left join user_profile up on u.id=up.id left join reward_point_account rpa on u.id=rpa.userId where u.type <> "system"';
        $params = [];
        if (!empty($conditions['nickname'])) {
            $sql .= ' and u.nickname like ?';
            $params[] = "%{$conditions['nickname']}%";
        }
        if (!empty($conditions['truename'])) {
            $sql .= ' and up.truename like ?';
            $params[] = "%{$conditions['truename']}%";
        }
        if ($orderBys) {
            $sql .= ' order by';
            foreach ($orderBys as $orderBy => $order) {
                $sql .= " {$orderBy} {$order},";
            }
            $sql .= ' u.id ASC';
        }
        $sql .= " limit {$start}, {$limit}";

        return $this->db()->fetchAll($sql, $params);
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
