<?php

namespace Biz\RewardPoint\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\RewardPoint\Dao\AccountDao;

class AccountDaoImpl extends GeneralDaoImpl implements AccountDao
{
    protected $table = 'reward_point_account';

    public function deleteByUserId($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE userId = ?";

        return $this->db()->executeUpdate($sql, array($userId));
    }

    public function getByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'userId = :userId',
                'userId IN ( :userIds)',
            )
        );
    }
}